module Properties
{
    root rootObject {
        string title;
    }
    entity entityObject{
        string str;
    }
    value valueObject {
        string str;
    }

    root valueRoot
    {
        valueObject prop;
        valueObject? null_prop;
        valueObject[] prop_arr;
        valueObject[]? null_prop_arr;
        valueObject?[] prop_arr_with_nulls;
        valueObject?[]? null_prop_arr_with_nulls;
    }

    root entityRefRoot
    {
        entityObject[] items;
    }

    root rootCollectionRoot
    {
        string str;
        rootObject[] *items;
        rootObject[] *itemsNull;
        rootObject[] *itemsSnap { snapshot; }
    }
}
