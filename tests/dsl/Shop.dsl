module Shop
{
    root Customer
    {
        string name;
    }

    root Product
    {
        string name;
        money price;
    }

    root Order
    {
        date created;
        Customer *customer { snapshot; }
    	Product[] *products { snapshot; }
    }
}
