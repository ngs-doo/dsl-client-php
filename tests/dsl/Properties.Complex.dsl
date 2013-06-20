module Properties
{
    root dateRoot
    {
        date prop;
        date? null_prop;
        date[] prop_arr;
        date[]? null_prop_arr;
        date?[] prop_arr_with_nulls;
        date?[]? null_prop_arr_with_nulls;
    }

    root binaryRoot
    {
        binary prop;
        binary? null_prop;
        binary[] prop_arr;
        binary[]? null_prop_arr;
        binary?[] prop_arr_with_nulls;
        binary?[]? null_prop_arr_with_nulls;
    }

    root decimalRoot
    {
        decimal prop;
        decimal? null_prop;
        decimal[] prop_arr;
        decimal[]? null_prop_arr;
        decimal?[] prop_arr_with_nulls;
        decimal?[]? null_prop_arr_with_nulls;
    }

    root decimalScaleRoot
    {
        decimal(2) prop;
        decimal(2)? null_prop;
        decimal(2)[] prop_arr;
        decimal(2)[]? null_prop_arr;
        decimal(2)?[] prop_arr_with_nulls;
        decimal(2)?[]? null_prop_arr_with_nulls;
    }

    root guidRoot
    {
        guid prop;
        guid? null_prop;
        guid[] prop_arr;
        guid[]? null_prop_arr;
        guid?[] prop_arr_with_nulls;
        guid?[]? null_prop_arr_with_nulls;
    }

    root locationRoot
    {
        location prop;
        location? null_prop;
        location[] prop_arr;
        location[]? null_prop_arr;
        location?[] prop_arr_with_nulls;
        location?[]? null_prop_arr_with_nulls;
    }

    root moneyRoot
    {
        money prop;
        money? null_prop;
        money[] prop_arr;
        money[]? null_prop_arr;
        money?[] prop_arr_with_nulls;
        money?[]? null_prop_arr_with_nulls;
    }

    root pointRoot
    {
        point prop;
        point? null_prop;
        point[] prop_arr;
        point[]? null_prop_arr;
        point?[] prop_arr_with_nulls;
        point?[]? null_prop_arr_with_nulls;
    }

    root s3Root
    {
        s3 prop;
        s3? null_prop;
        s3[] prop_arr;
        s3[]? null_prop_arr;
        s3?[] prop_arr_with_nulls;
        s3?[]? null_prop_arr_with_nulls;
    }

    root timestampRoot
    {
        timestamp prop;
        timestamp? null_prop;
        timestamp[] prop_arr;
        timestamp[]? null_prop_arr;
        timestamp?[] prop_arr_with_nulls;
        timestamp?[]? null_prop_arr_with_nulls;
    }
}
