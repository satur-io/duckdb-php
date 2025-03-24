#!/bin/bash
cleanup() {
  printf "Cleaning up...\n\n";
  rm -rf /tmp/main_branch_* /tmp/new_branch_* /tmp/duckdb_cli_* || true
  rm -rf temp_stats_duckdb_cli temp_stats_main temp_stats_new_branch || true
  rm -rf /tmp/master-branch || true
  exit;
}

trap "exit" INT
trap cleanup EXIT

function run_query() {
  echo "${bold}${3}${normal}";
  QUERY="${1}";
  DATABASE="$(echo -e "${2}" | tr -d '[:space:]')";

  if [[ ${DATABASE} ]]; then
    DATABASE_MAIN="/tmp/main_branch_${DATABASE}"
    DATABASE_NEW="/tmp/new_branch_${DATABASE}"
    DATABASE_CLI="/tmp/duckdb_cli_${DATABASE}"
  else
    DATABASE_MAIN=""
    DATABASE_NEW=""
    DATABASE_CLI=""
  fi
  execution_time_new_branch=0
  memory_usage_new_branch=0
  execution_time_main_branch=0
  memory_usage_main_branch=0
  execution_time_duckdb_cli=0
  memory_usage_duckdb_cli=0

  for i in $(seq 0 ${ITERATIONS})
  do
    gtime -f "%e %M" -o temp_stats_main ${MAIN_BRANCH_COMMAND} "${QUERY}" "${DATABASE_MAIN}" &> /dev/null
    gtime -f "%e %M" -o temp_stats_new_branch ${NEW_BRANCH_COMMAND} "${QUERY}" "${DATABASE_NEW}" &> /dev/null
    gtime -f "%e %M" -o temp_stats_duckdb_cli ${REFERENCE_DUCKDB_CLI} "${QUERY}" "${DATABASE_CLI}" &> /dev/null

    execution_time_new_branch=$(bc -l <<< "${execution_time_new_branch} + $(awk '{print $1}' < temp_stats_new_branch)");
    memory_usage_new_branch=$(bc -l <<< "${memory_usage_new_branch} + $(awk '{print $2}' < temp_stats_new_branch)");

    execution_time_main_branch=$(bc -l <<< "${execution_time_main_branch} + $(awk '{print $1}' < temp_stats_main)");
    memory_usage_main_branch=$(bc -l <<< "${memory_usage_main_branch} + $(awk '{print $2}' < temp_stats_main)");

    execution_time_duckdb_cli=$(bc -l <<< "${execution_time_duckdb_cli} + $(awk '{print $1}' < temp_stats_duckdb_cli)");
    memory_usage_duckdb_cli=$(bc -l <<< "${memory_usage_duckdb_cli} + $(awk '{print $2}' < temp_stats_duckdb_cli)");
  done


  time_percentage=$(bc -l <<< "${execution_time_new_branch} / ${execution_time_main_branch}");
  mem_percentage=$(bc -l <<< "${memory_usage_new_branch} / ${memory_usage_main_branch}");
  time_percentage_with_duckdb_cli=$(bc -l <<< "${execution_time_new_branch} / ${execution_time_duckdb_cli}");
  mem_percentage_with_duckdb_cli=$(bc -l <<< "${memory_usage_new_branch} / ${memory_usage_duckdb_cli}");

  mean_time_new_branch=$(bc -l <<< "${execution_time_new_branch} / ${ITERATIONS}");
  mean_time_main_branch=$(bc -l <<< "${execution_time_main_branch} / ${ITERATIONS}");
  mean_time_duckdb_cli=$(bc -l <<< "${execution_time_duckdb_cli} / ${ITERATIONS}");

  mean_mem_new_branch=$(bc -l <<< "${memory_usage_new_branch} / ${ITERATIONS}");
  mean_mem_main_branch=$(bc -l <<< "${memory_usage_main_branch} / ${ITERATIONS}");
  mean_mem_duckdb_cli=$(bc -l <<< "${memory_usage_duckdb_cli} / ${ITERATIONS}");

  printf "%-15.15s %-10.10s   %-10.10s \n" "api" "Time" "Mem"
  printf "%-15.15s %-10.10s   %-10.10s \n" "DuckDB CLI" "${mean_time_duckdb_cli}" "${mean_mem_duckdb_cli}"
  printf "%-15.15s %-10.10s   %-10.10s \n" "New branch" "${mean_time_new_branch}" "${mean_mem_new_branch}"
  printf "%-15.15s %-10.10s   %-10.10s \n" "Main branch" "${mean_time_main_branch}" "${mean_mem_main_branch}"

  echo
  echo Time diff with DuckDB CLI: "${time_percentage_with_duckdb_cli}"
  echo Mem diff with DuckDB CLI: "${mem_percentage_with_duckdb_cli}"

  echo
  if (( $(echo "(($mean_time_new_branch-$mean_time_main_branch) > ${MIN_TIME_DIFFF_TO_CHECK_PERCENTAGE}) && $time_percentage > ${MAX_TIME_PERCENTAGE_INCREASE_ALLOWED}" |bc -l) )); then
    errors=$((errors+1))
    >&2 echo "${bold}${red}Bad performance. Time percentage diff ${time_percentage} > 1.1${black}${normal}";
  else
    echo "${bold}${green}Performance OK: ${time_percentage}${black}${normal}"
  fi
    if (( $(echo "$mem_percentage > ${MAX_MEMORY_PERCENTAGE_INCREASE_ALLOWED}" |bc -l) )); then
      errors=$((errors+1))
      >&2 echo "${bold}${red}Bad performance. Memory percentage diff ${mem_percentage} > 1.1${black}${normal}";
    else
      echo "${bold}${green}Mem OK: ${mem_percentage}${black}${normal}"
    fi
  echo
  echo

  if [ "${GENERATE_PLOTS}" = true ]
  then
    generate_plots "${3}" "${mean_time_duckdb_cli}" "${mean_mem_duckdb_cli}" "${mean_time_new_branch}" "${mean_mem_new_branch}" "${mean_time_main_branch}" "${mean_mem_main_branch}"
  fi
}


function generate_plots() {
  FILE="/tmp/plot_data.data";
  printf "\"%s\"\n" "${1}" > ${FILE}
  printf "API \t Time \t Mem \n" >> ${FILE}
  printf "\"DuckDB CLI\" \t %10.4f \t %10.4f \n" "${2}" "${3}" >> ${FILE}
  printf "\"DuckDB PHP\" \t %10.4f \t %10.4f \n" "${4}" "${5}" >> ${FILE}

  OUTPUT_PLOT_FILE="out/$(cat /dev/urandom | env LC_CTYPE=alnum tr -cd 'a-f0-9' | head -c 32).png"
  gnuplot -e "set output '${OUTPUT_PLOT_FILE}'" test/Performance/commands.txt
  rm ${FILE};
}

start=$(date +%s.+%N)
errors=0

bold=$(tput bold)
normal=$(tput sgr0)
red=$(tput setaf 1)
green=$(tput setaf 2)
black=$(tput setaf 0)

ITERATIONS=5
GENERATE_PLOTS=true
MIN_TIME_DIFFF_TO_CHECK_PERCENTAGE="0.02" # Difference less than 20ms is considered no performance degradation regardless the percentage
MAX_TIME_PERCENTAGE_INCREASE_ALLOWED="1.1" # An increase of 10% in time is considered performance degradation
MAX_MEMORY_PERCENTAGE_INCREASE_ALLOWED="1.1" # An increase of 10% in memory usage is considered performance degradation

rm -rf /tmp/master-branch
git clone --branch main --depth 1 file://${PWD} /tmp/master-branch

orig=${PWD}

rm -rf /tmp/master-branch/test/_data
ln -s ${PWD}/test/_data /tmp/master-branch/test/_data

# rm -rf /tmp/master-branch/test/Performance/duckdb_api
# cp test/Performance/duckdb_api /tmp/master-branch/test/Performance/duckdb_api

cp preload.php /tmp/master-branch

#printf "opcache.enable=0\nopcache.enable_cli=0" > /tmp/master-branch/.user.ini;

cd  /tmp/master-branch || exit ;
# git checkout f7dd1e659dbf5db9b51948422514209bc6a95f90;
PHP_INI_SCAN_DIR=${PHP_INI_SCAN_DIR}:${PWD} composer dump-autoload;
rm -f /tmp/master-branch/.user.ini;
cd "${orig}" || exit

NEW_BRANCH_COMMAND="test/Performance/duckdb_api_batches";
MAIN_BRANCH_COMMAND="/tmp/master-branch/test/Performance/duckdb_api";
REFERENCE_DUCKDB_CLI="duckdb --list -c";

${NEW_BRANCH_COMMAND} "SELECT 1;" > /dev/null 2>&1
${MAIN_BRANCH_COMMAND} "SELECT 1;" > /dev/null 2>&1
${REFERENCE_DUCKDB_CLI} "SELECT 1;" > /dev/null 2>&1

FILE=$1;

while IFS= read -r line; do
  IFS=! query_parts=(${line//--/!});
  unset IFS;
  run_query "${query_parts[0]}" "${query_parts[1]}" "${query_parts[2]}"
done < "${FILE}"

end=$(date +%s.%N)
runtime=$( echo "$end - $start" | bc -l )

printf "Tests finished in %9.4f seconds" ${runtime}

if (( $(echo "$errors > 0" |bc -l) )); then
  echo
  echo "${red}-----------------------------------------------"
  printf "${bold}  %s performance and memory error(s)${normal}  \n" ${errors}
  echo "${red}-----------------------------------------------"
  echo
  exit 1
else
  echo
  echo "${green}----------------------------------------------"
  printf "${bold}  🦆🦆🦆 No performance degradation! 🐘🐘🐘${normal}\n"
  echo "${green}----------------------------------------------"
  echo
fi

