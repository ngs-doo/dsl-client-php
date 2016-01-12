module Test
{
    root Foo (bar)
    {
        string bar;
        int num;

        specification searchByBar 'it => it.bar.StartsWith(name)'
        {
            string name;
        }

        history;

/*      event AddNum 'it => it.num += amount'
        {
            int amount;
        }
*/

        specification greaterThan 'it => it.num > min'
        {
            int min;
        }
    }
    /* TODO java-test */
/*
    event Foo.AddNum
    {
        int amount;
    }
*/

    root Bar(ID)
    {
        int ID { sequence; }
    }

    root RootWithEntity (name)
    {
        string name;
        EntityTest ent;
        EntityTest1[] entarr;
    }

    entity EntityTest
    {
        string name;
        string[] strArr;
        integer[] intArr;
    }

    entity EntityTest1
    {
        string name;
        ValueTest val;
    }

    value ValueTest
    {
        string name;
    }

    report FooReport
    {
        string uri;
        Foo foo 'it => it.URI == uri';

        templater CreatePdf 'test.docx' pdf;
        templater CreateXml 'template.txt';
    }

    snowflake FooGrid from Foo
    {
        bar;
        num;
    }

    cube FooCube from FooGrid
    {
        dimension bar;
        dimension num;
        count num as count;
        sum num as total;
        average num as average;

        specification findByBar
            'it => it.bar.StartsWith(query)'
        {
            string query;
        }

        templater createPdf 'template.txt' pdf;
        templater createXml 'template.txt';
    }

    root File
    {
        binary Content;
    }

    root FileS3
    {
        string Name;
        s3 Content;
    }

    root Address
    {
        string Name;
        string(5) PostalCode;
        location At;
        point P;
    }

    root Shape
    {
        Point[] points;
        Point[]? normals;
        Timestamp[]? times;
    }

    root AreaMap
    {
        Location[] bars;
        Location[]? stores;
    }

    root FooFoo (bar)
    {
        string bar;
        int num;
        specification searchByBar 'it => it.bar.StartsWith(name)'
        {
            string name;
        }
    }

    root Item (bar)
    {
        string bar;
        int num;

        specification greaterThan 'it => it.num > min'
        {
            int min;
        }
    }

    entity EntCompKey
    {
        string name;
        int code;
    }

    root ItemHolder
    {
        Item *item;
        Item? *optItem;
    }

    event SomeEvent
    {
        string name;
    }

    value tmpCompile
    {
        string abcdd;
    }

    root Typer
    {
        map map;
    }

    root Elem
    {
        xml data;
    }

    root recompileMePlease
    {
        string v2;
    }

    /* TODO java-test */
    /*enum en;*/
/*
    root EnumFoo
    {
        en? emptyEnum;
    }*/
}
