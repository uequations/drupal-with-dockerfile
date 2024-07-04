# Drupal on Docker Deployed to Azure App Services

## Git
Azure DevOps instance located at:https://dev.azure.com/uequations-2828/Drupal-With-Dockerfile/_build

## Deploy Latest Image
Go to the Deployment Center and update the tag number for the for the image.

## Azure Blob File Storage
```
az storage account keys list --resource-group drupal-custom_rg --account-name drupalcustomstorage
```