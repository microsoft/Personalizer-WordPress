using Microsoft.Azure.CognitiveServices.Personalizer;
using Microsoft.Azure.WebJobs;
using Microsoft.Extensions.Configuration;

namespace Microsoft.Personalizer.CMS
{
    public static class PersonalizerUtil
    {
        public static IPersonalizerClient GetPersonalizerClient(ExecutionContext context)
        {
            // get the configuration
            var config = new ConfigurationBuilder()
                .SetBasePath(context.FunctionAppDirectory)
                .AddJsonFile("local.settings.json", optional: true, reloadOnChange: true)
                .AddEnvironmentVariables()
                .Build();

            var personalizerEndpoint = config["CogService_Personalizer_Endpoint"];
            var personalizerKey = config["CogService_Personalizer_Key"];

            return new PersonalizerClient(
                new ApiKeyServiceClientCredentials(personalizerKey))
            { Endpoint = personalizerEndpoint };
        }
    }
}
