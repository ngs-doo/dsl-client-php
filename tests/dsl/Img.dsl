module Img
{
    enum Extension {
        jpg;
        png;
        bmp;
    }

    Value Icon
    {
        int code;
        string? description;
        bool[]? bitmask;
        float[] polygon;
        Extension? ext;
    }

    root Bitmap
    {
        Icon primary;
        Icon? secondary;
        Icon[] auxylliary;
        Icon[]? accessory;
        Extension ext;
    }

    root Album
    {
        string title;
        Bitmap[] *bmps;
        Icon?[]? icons;
        Extension[] allowed;
    }
}
