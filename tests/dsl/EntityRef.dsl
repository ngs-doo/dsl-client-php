module EntityRef
{
    Entity ent(key)
    {
        string key;
    }

    root foo
    {
        ent *ref;
    }

    root bar {
        ent *ref;
        string ab;
    }

    Entity Comment(email)
    {
        string email;
        string title;
    }

    root Blog
    {
        Comment[] *comments;
    }
}
