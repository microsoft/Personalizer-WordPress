namespace Microsoft.Personalizer.CMS.Request
{
    public class WordPressRankRequestSite
    {
        /// <summary>
        /// get_bloginfo('name')
        /// 
        /// https://developer.wordpress.org/reference/functions/get_bloginfo/
        /// </summary>
        public string Name { get; set; }

        /// <summary>
        /// get_bloginfo('description')
        /// 
        /// https://developer.wordpress.org/reference/functions/get_bloginfo/
        /// </summary>
        public string Tagline { get; set; }

        /// <summary>
        /// get_bloginfo('language')
        /// 
        /// https://developer.wordpress.org/reference/functions/get_bloginfo/
        /// </summary>
        public string Language { get; set; }
    }
}
