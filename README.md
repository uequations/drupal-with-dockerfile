# Drupal on Docker Deployed to Azure App Services

## Git
Azure DevOps instance located at:https://dev.azure.com/uequations-2828/Drupal-With-Dockerfile/_build

## Deploy Latest Image
Go to the Deployment Center and update the tag number for the for the image. Once this is done and the change is saved, you won't need to restart the container to see the changes.

## Azure Blob File Storage
```
az storage account keys list --resource-group drupal-custom_rg --account-name drupalcustomstorage
```