using System.Collections.Generic;

namespace Microsoft.Personalizer.CMS.Request
{
    public class WordPressRankRequestPost
    {
        /// <summary>
        /// The post id
        /// </summary>
        public int ID { get; set; }

        /// <summary>
        /// The post URL.
        /// </summary>
        public string URL { get; set; }

        /// <summary>
        /// https://developer.wordpress.org/reference/functions/get_the_title/
        /// </summary>
        public string Title { get; set; }

        public string ImageURL { get; set; }

        /// <summary>
        /// https://developer.wordpress.org/reference/functions/the_excerpt/
        /// </summary>
        public string Excerpt { get; set; }

        /// <summary>
        /// https://developer.wordpress.org/reference/functions/wp_get_post_categories/
        /// 
        /// Using the name for now. There is slug, term_group, taxonomy, description, ... https://developer.wordpress.org/reference/classes/wp_term/
        /// </summary>
        public List<string> Categories { get; set; }

        /// <summary>
        /// https://developer.wordpress.org/reference/functions/get_the_tags/
        /// </summary>
        public List<string> Tags { get; set; }
    }
}
