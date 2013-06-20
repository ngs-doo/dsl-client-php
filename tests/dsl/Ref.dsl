module Ref
{
    root Par
    {
        Ent[] entArr;
        Child *child;
        Ent2 ent;
        Simple *simple;
        Ent3[] ent3Arr;
    }

    root Simple
    {
    }

    root Child
    {
        Ent4[] ent4;
    }

    entity Ent
    {
        Simple *simple;
        Ent5[] ent5Arr;
    }

    entity Ent2
    {
        Simple *simple;
    }

    entity Ent3
    {
        Simple *simple;
    }

    entity Ent4
    {
        Simple *simple;
    }

    entity Ent5
    {

    }
}
