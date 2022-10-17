#!/usr/bin/with-contenv bash

CONF_POSTGRESQL=/etc/postgresql.conf

if [[ $POSTGRES_MAX_CONNECTIONS ]]
then
  echo "[postgresql] setting max_connections ${POSTGRES_MAX_CONNECTIONS}"
  sed -i "s/max_connections=.*/max_connections=${POSTGRES_MAX_CONNECTIONS}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_DEFAULT_STATISTICS_TARGET ]]
then
  echo "[postgresql] setting default_statistics_target ${POSTGRES_DEFAULT_STATISTICS_TARGET}"
  sed -i "s/default_statistics_target=.*/default_statistics_target=${POSTGRES_DEFAULT_STATISTICS_TARGET}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_SHARED_BUFFERS ]]
then
  echo "[postgresql] setting shared_buffers ${POSTGRES_SHARED_BUFFERS}"
  sed -i "s/shared_buffers=.*/shared_buffers=${POSTGRES_SHARED_BUFFERS}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_MAX_PARALLEL_WORKERS ]]
then
  echo "[postgresql] setting max_parallel_workers ${POSTGRES_MAX_PARALLEL_WORKERS}"
  sed -i "s/max_parallel_workers=.*/max_parallel_workers=${POSTGRES_MAX_PARALLEL_WORKERS}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_MAX_PARALLEL_WORKERS_PER_GATHER ]]
then
  echo "[postgresql] setting max_parallel_workers_per_gather ${POSTGRES_MAX_PARALLEL_WORKERS_PER_GATHER}"
  sed -i "s/max_parallel_workers_per_gather=.*/max_parallel_workers_per_gather=${POSTGRES_MAX_PARALLEL_WORKERS_PER_GATHER}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_WORK_MEM ]]
then
  echo "[postgresql] setting work_mem ${POSTGRES_WORK_MEM}"
  sed -i "s/work_mem=.*/work_mem=${POSTGRES_WORK_MEM}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_WALL_BUFFERS ]]
then
  echo "[postgresql] setting wal_buffers ${POSTGRES_WALL_BUFFERS}"
  sed -i "s/wal_buffers=.*/wal_buffers=${POSTGRES_WALL_BUFFERS}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_MAINTENANCE_WORK_MEM ]]
then
  echo "[postgresql] setting maintenance_work_mem ${POSTGRES_MAINTENANCE_WORK_MEM}"
  sed -i "s/maintenance_work_mem=.*/maintenance_work_mem=${POSTGRES_MAINTENANCE_WORK_MEM}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_EFFECTIVE_CACHE_SIZE ]]
then
  echo "[postgresql] setting effective_cache_size ${POSTGRES_EFFECTIVE_CACHE_SIZE}"
  sed -i "s/effective_cache_size=.*/effective_cache_size=${POSTGRES_EFFECTIVE_CACHE_SIZE}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_RANDOM_PAGE_COST ]]
then
  echo "[postgresql] setting random_page_cost ${POSTGRES_RANDOM_PAGE_COST}"
  sed -i "s/random_page_cost=.*/random_page_cost=${POSTGRES_RANDOM_PAGE_COST}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_SYNCHRONOUS_COMMIT ]]
then
  echo "[postgresql] setting synchronous_commit ${POSTGRES_SYNCHRONOUS_COMMIT}"
  sed -i "s/synchronous_commit=.*/synchronous_commit=${POSTGRES_SYNCHRONOUS_COMMIT}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_LOG_MIN_DURATION_STATEMENT ]]
then
  echo "[postgresql] setting log_min_duration_statement ${POSTGRES_LOG_MIN_DURATION_STATEMENT}"
  sed -i "s/log_min_duration_statement=.*/log_min_duration_statement=${POSTGRES_LOG_MIN_DURATION_STATEMENT}/" $CONF_POSTGRESQL
fi

if [[ $POSTGRES_AUTOVACUUM ]]
then
  echo "[postgresql] setting autovacuum ${POSTGRES_AUTOVACUUM}"
  sed -i "s/autovacuum=.*/autovacuum=${POSTGRES_AUTOVACUUM}/" $CONF_POSTGRESQL
fi
