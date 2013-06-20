module Properties
{
    root boolRoot
    {
        bool prop;
        bool? null_prop;
        bool[] prop_arr;
        bool[]? null_prop_arr;
        bool?[] prop_arr_with_nulls;
        bool?[]? null_prop_arr_with_nulls;
    }
    root doubleRoot
    {
        double prop;
        double? null_prop;
        double[] prop_arr;
        double[]? null_prop_arr;
        double?[] prop_arr_with_nulls;
        double?[]? null_prop_arr_with_nulls;
    }
    root floatRoot
    {
        float prop;
        float? null_prop;
        float[] prop_arr;
        float[]? null_prop_arr;
        float?[] prop_arr_with_nulls;
        float?[]? null_prop_arr_with_nulls;
    }
    root intRoot
    {
        int prop;
        int? null_prop;
        int[] prop_arr;
        int[]? null_prop_arr;
        int?[] prop_arr_with_nulls;
        int?[]? null_prop_arr_with_nulls;
    }
    root longRoot
    {
        long prop;
        long? null_prop;
        long[] prop_arr;
        long[]? null_prop_arr;
        long?[] prop_arr_with_nulls;
        long?[]? null_prop_arr_with_nulls;
    }
    root stringRoot
    {
        string prop;
        string? null_prop;
        string[] prop_arr;
        string[]? null_prop_arr;
        string?[] prop_arr_with_nulls;
        string?[]? null_prop_arr_with_nulls;
    }

    root stringWithLengthRoot
    {
        string(10) prop;
        string(10)? null_prop;
        string(10)[] prop_arr;
        string(10)[]? null_prop_arr;
        string(10)?[] prop_arr_with_nulls;
        string(10)?[]? null_prop_arr_with_nulls;
    }
}
