module Cache
{
    big aggregate Transaction {
        String  realmID;
        String  scrapeID;
        Int     accountIndex;
        Int     transactionIndex;
        Unique(realmID, scrapeID, accountIndex,
transactionIndex);

        String  bankAbbr;
        String  accountNumber;
        Date    onDate;
        String  description;
        Money   amount;
        Money?  balance;

        String  slug { Index; }
    }

    aggregate ProcessedTransaction(transactionID) {
        Long  transactionID;
        Relationship transactionID (transactionID) Transaction;
    }

    aggregate RefToPT {
        ProcessedTransaction *PT1;
        ProcessedTransaction? *PT2;
    }

    snowflake<RefToPT> RefToPTSnow { PT1; PT2; }

    aggregate Slug(slug, realmID) {
        String  realmID;
        String  slug;
        String  description;
        Int     hits { Index; }
    }

    aggregate FilteredSlug(slug, realmID, filterID) {
        String  realmID;
        String  slug;
        String  filterID;
        Int     hits;
    }
}
