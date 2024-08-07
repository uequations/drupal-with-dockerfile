# Drupal on Docker Deployed to Azure App Services

## Git
Azure DevOps instance located at:https://dev.azure.com/uequations-2828/Drupal-With-Dockerfile/_build

## Deploy Latest Image
Go to the Deployment Center and update the tag number for the for the image. Once this is done and the change is saved, you won't need to restart the container to see the changes.

## Azure Blob File Storage
```
az storage account keys list --resource-group <resource-group-name> --account-name <storage-account-name>
```

## Azure SSH Into Container
```
$ az webapp create-remote-connection --subscription <subscription-id> --resource-group <resource-group-name> -n <app-name> &
```

If needed
```
az webapp config set --resource-group <resource-group-name> -n <app-name> --remote-debugging-enabled=false
```

## Logs
```
az webapp log tail --resource-group <resource-groups> --name <app-name>
```