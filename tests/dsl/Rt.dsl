module Rt
{
    root rt
    {
        int? num;
        ref(num)? *ref;
    }

    root ref(serial)
    {
        int serial;
        rt(serial) *rt;
    }

    root selfRef
    {
        string a;
        selfRef? *refer;
        calculated string nested from 'it => it.refer.refer.refer.a';
        calculated string fromNested from 'it => it.nested';
        detail parent selfRef.refer;
    }
}
