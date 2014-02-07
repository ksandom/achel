# Functionality for creating a wizard

function inputValidation_groupRegex
{
	fields="$1"
	regex="$2"
	comment="$3"
	prefix="$4"
	
	result=0
	
	for field in $fields; do
		fieldName="$prefix$field"
		if echo "${!fieldName}" | grep -q "$regex" ; then
			echo "Field \"$field\" fails (matches) regex \"$regex\". Comment: $comment"
			result=1
		fi
	done
	
	return $result;
}

function inputValidation_expectLines
{
	fields="$1"
	lines="$2"
	comment="$3"
	prefix="$4"
	
	result=0
	
	for field in $fields; do
		fieldName="$prefix$field"
		if [ "`echo \"${!fieldName}\" | wc -l`" -gt $lines ]; then
			echo "Field \"$field\" fails requirement for number of lines. Comment: $comment"
			result=1
		fi
	done
	
	return $result;
}
