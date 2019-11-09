namespace Microsoft.Personalizer.CMS.Request
{
    public class WordPressRankRequestUser
    {
        /// <summary>
        /// $user_agent = $_SERVER['HTTP_USER_AGENT']; 
        /// </summary>
        public string UserAgent { get; set; }

        /// <summary>
        /// $userInfo = geoip_detect2_get_info_from_current_ip();
        /// 
        /// $userInfo->country->isoCode
        /// </summary>
        public string CountryISO { get; set; }

        /// <summary>
        /// $userInfo = geoip_detect2_get_info_from_current_ip();
        /// 
        /// $userInfo->mostSpecificSubdivision->isoCode
        /// </summary>
        public string StateISO { get; set; }

        // TODO
        // hash(UserId)
        // History()
    }
}
