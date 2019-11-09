using System;
using System.Threading.Tasks;
using Microsoft.AspNetCore.Mvc;
using Microsoft.Azure.WebJobs;
using Microsoft.Azure.WebJobs.Extensions.Http;
using Microsoft.AspNetCore.Http;
using Microsoft.Extensions.Logging;
using System.Threading;
using Microsoft.Azure.CognitiveServices.Personalizer;

namespace Microsoft.Personalizer.CMS
{
    public static class Reward

    {
        [FunctionName("Reward")]
        public static async Task<IActionResult> Run(
            [HttpTrigger(AuthorizationLevel.Anonymous, "get", "post", Route = null)] HttpRequest req,
            ILogger log,
            CancellationToken cancellationToken,
            Microsoft.Azure.WebJobs.ExecutionContext context)
        {
            log.LogInformation("Reward");

            try
            {
                var eventId = req.Query["eventId"];

                var client = PersonalizerUtil.GetPersonalizerClient(context);
                await client.RewardAsync(eventId, 1, cancellationToken);

                return new OkResult();
            }
            catch (Exception e)
            {
                return new BadRequestObjectResult(e.Message);
            }
        }
    }
}
