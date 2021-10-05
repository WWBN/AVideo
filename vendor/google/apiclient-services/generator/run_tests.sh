#!/bin/sh
#
# Run the Google APIs client generator tests
#

case $0 in
  /* )
   TOP=$(dirname "$0")
   ;;
  * )
   TOP=$(dirname "$(/bin/pwd)/$0")
   ;;
esac

export PYTHONPATH=$TOP/src

TMP_REPORT_DIR=$(mktemp -d)
SUCCEEDED_FILE=${TMP_REPORT_DIR}/succeeded
FAILED_FILE=${TMP_REPORT_DIR}/failed

cd $TOP/tests
find . -name '*_test.py' | while read test
do
  python $test
  if [ $? == 0 ]; then
    echo "${test}" >> "${SUCCEEDED_FILE}"
  else
    echo "${test}" >> "${FAILED_FILE}"
  fi
done

echo ""
echo "--------- Succeeded tests -----------"
cat "${SUCCEEDED_FILE}"

if [ -f "${FAILED_FILE}" ]; then
    echo "--------- Failed tests --------------"
    cat "${FAILED_FILE}"
    echo "-------------------------------------"
    exit 1
fi
echo "-------------------------------------"
