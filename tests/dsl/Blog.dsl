module Blog
{
    root Post
    {
        string Title;
        string Content;
        date CreatedAt;

        Comment[] Comments;

        /* TODO java-test */
        /*calculated HasComments from 'it => it.Comments.Any()';*/

        /* TODO java-test */
        /*calculated string[]? UsersEmails from 'it => it.Comments.Select(c => c.User.Email).ToArray()';
        calculated string?[]? UsersNames from 'it => it.Comments.Select(c => c.User.Name).ToArray()';
        calculated date[] CommentDates from 'it => it.Comments.Select(c => c.CreatedAt).ToArray()';*/

        calculated HasCommentsWithPositiveScore from 'it => it.Comments.Any(c => c.HasPositiveScore)';

        specification findByTitle 'it => it.Title == query' {
            string query;
        }
    }

    snowflake PostView from Post
    {
        Title;
        Content;
    }

    entity Comment
    {
        User? *User;
        string Content;
        int Score;
        date CreatedAt;

        calculated HasPositiveScore from 'it => it.Score>0';
    }

    root User (Email)
    {
        string Email;
        string? Name;
    }

    value ab{}

}
