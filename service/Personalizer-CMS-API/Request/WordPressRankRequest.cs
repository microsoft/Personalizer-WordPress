using System.Collections.Generic;

namespace Microsoft.Personalizer.CMS.Request
{
    public class WordPressRankRequest
    {
        public WordPressRankRequestUser User { get; set; }

        public WordPressRankRequestSite Site { get; set; }

        public List<WordPressRankRequestPost> Posts { get; set; }
    }
}
