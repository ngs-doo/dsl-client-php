#!/usr/bin/env bash
DSL=./tests/dsl
CLC=./tests/dsl-clc.jar
COMPILER=./tmp/dsl-compiler.exe
java -jar ${CLC} -dsl=${DSL} -compiler=${COMPILER} -migration -apply -force -postgres="localhost:5432/dsl_client_php_test?user=postgres&password=6666"
java -jar ${CLC} -dsl=${DSL} -compiler=${COMPILER} -target=php_client -legacy -active-record
java -jar ${CLC} -dsl=${DSL} -compiler=${COMPILER} -target=revenj.java  -dependencies=. -dsl=tests/dsl -jackson -namespace=test
