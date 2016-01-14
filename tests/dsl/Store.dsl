module Store
{
    root Product
    {
        string Name;
        money Price;
        Product? *ParentProduct;
        Group? *Group;
        timestamp ModifiedAt { versioning; }
        detail Packages from Package.Product;
    }

    snowflake ProductList from Product
    {
        Name;
        Price;
        Packages;
        order by Price desc, Name asc;

        specification findProductsWithPackages 'it => it.Packages.Any()';
    }

    root Group
    {
        string Name;
        Group? *Parent;
        detail Products from Product.Group;
    }

    root Package
    {
        Product *Product;
    }

}
