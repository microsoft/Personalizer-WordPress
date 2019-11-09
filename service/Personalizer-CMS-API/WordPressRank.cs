using System;
using System.IO;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Extensions.Http;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using Newtonsoft.Json;
using Microsoft.Azure.CognitiveServices.Personalizer;
using Microsoft.Azure.CognitiveServices.Personalizer.Models;
using System.Threading;
using System.Collections.Generic;
using Microsoft.Personalizer.CMS.Request;
using UAParser;
using System.Linq;
using System.Globalization;

namespace Microsoft.Personalizer.CMS
{
    public static class WordPressRank
    {
        private static Dictionary<string, int> StringToDictionary(string text)
        {
            return text?.Split(null).ToDictionary(word => word, word => 1);
        }

        [FunctionName("WordPressRank")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Anonymous, "post", Route = null)] HttpRequest req,
            ILogger log,
            CancellationToken cancellationToken,
            Microsoft.Azure.WebJobs.ExecutionContext context)
        {
            log.LogInformation("Rank");

            try
            {
                string requestBody;
                using (var bodyReader = new StreamReader(req.Body))
                {
                    requestBody = await bodyReader.ReadToEndAsync(); 
                }
                 
                var request = JsonConvert.DeserializeObject<WordPressRankRequest>(requestBody);

                var userAgentParsed = Parser.GetDefault().Parse(request.User.UserAgent);

                var client = PersonalizerUtil.GetPersonalizerClient(context);

                var rankResponse = await client.RankAsync(new RankRequest
                {
                    ContextFeatures = new List<object>
                    {
                        // user context features are lower-case namespaces
                        new
                        {
                            // top-level properties are VowpalWabbit namespaces
                            location = new
                            {
                                request.User?.CountryISO,
                                request.User?.StateISO
                            },
                            device = new
                            {
                                OSFamily = userAgentParsed?.OS?.Family,
                                UAFamily = userAgentParsed?.UA?.Family,
                                DeviceFamily = userAgentParsed?.Device?.Family,
                                DeviceBrand = userAgentParsed?.Device?.Brand,
                                DeviceModel = userAgentParsed?.Device?.Model
                            },
                            asite = StringToDictionary(request.Site.Name),
                            bsite = StringToDictionary(request.Site.Tagline),
                            site = new
                            {
                                request.Site.Language
                            }
                        }
                    },
                    Actions = request.Posts.Select(post => new RankableAction
                    {
                        Id = post.ID.ToString(CultureInfo.InvariantCulture),
                        Features = new List<object>
                    {
                        new
                        {
                            // action features are upper-case namespaces
                            Atitle = StringToDictionary(post.Title),
                            Bexcerpt = StringToDictionary(post.Excerpt),
                            Tags = post.Tags?.ToDictionary(tag => tag, tag => 1),
                            Categories = post.Categories?.ToDictionary(cat => cat, cat => 1),
                            _URL = post.URL
                        }
                    }
                    }).ToList()
                }, cancellationToken);

                return new OkObjectResult(rankResponse);
            }
            catch(Exception e)
            {
                return new BadRequestObjectResult(e.Message);
            }
        }
    }
}
