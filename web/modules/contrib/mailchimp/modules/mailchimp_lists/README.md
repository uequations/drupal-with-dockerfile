Synchronize Drupal entities with Mailchimp audiences and allow anyone with
access to edit entities (such as Users editing their own data) to subscribe,
unsubscribe, and update membership information.

## Installation

1. Enable the Mailchimp Audiences module, the Field UI module,
and the Entity Module

2. If you haven't done so already, add an audience in your Mailchimp account.
Follow these directions provided by Mailchimp on how to
[add or import an audience](https://mailchimp.com/help/import-contacts-mailchimp/).

## Usage
* Subscription Field
Create an entity type with an email address field, or pick an entity that has an
email address property (like Users). Add a field to the entity type of the type
"Mailchimp Subscription" and use the field UI to configure your Subcription
field.

* Merge Fields
You will see Merge Field options based on the configuration of your audience
through Mailchimp. You can match these fields up to fields on your entity to
push your entity field values back to Mailchimp during subscriptions.

## Field-level Settings

* Require subscribers to Double Opt-in
New subscribers will be sent a link with an email from Mailchimp that they must
follow to confirm their subscription, rather than being immediately subscribed
and send a confirmation message from Mailchimp.

## Rules (and Roles)
There is a single Rules action to subscribe or unsubscribe Entities with a
configured Mailchimp Subscription field. This can be used to simply re-create
the Roles-based auto-subscription functionality in earlier versions of Mailchimp
audiences. Simply create a Mailchimp Subscription field on Users and hide the
field from them in the UI, then create a rule that subscribes users to this
audiences when they are saved, based on their role.

A sample Rules configuration export is provided here. This assigns based on Role
"3" and targets a field called field_members:

{ "rules_member_subscriptions" : {
    "LABEL" : "Member Subscriptions",
    "PLUGIN" : "reaction rule",
    "OWNER" : "rules",
    "REQUIRES" : [ "rules", "mailchimp_lists" ],
    "ON" : { "user_presave" : [] },
    "IF" : [
      { "user_has_role" : {
          "account" : [ "account" ], "roles" : { "value" : { "3" : "3" } } }
        }
      }
    ],
    "DO" : [
      { "mailchimp_lists_user_subscribe" : {
          "entity" : [ "account" ],
          "field" : [ "account:field-subscribers" ],
          "subscribe" : 1
        }
      }
    ]
  }
}

## Webhooks

Direct your browser to: admin/config/services/mailchimp
You will now see an "Audiences" tab. (admin/config/services/mailchimp/lists)
This should show you your audiences, and allow you to control Webhook settings
for each audience.

What does this mean?
When a user unsubscribes from a audience or updates their profile through
Mailchimp rather than Drupal, Mailchimp will trigger an event to update the
user's cached Mailchimp member information. This will not update any of their
merge field data, or any other Entity data: it just changes the cached
information. This cached data means Drupal doesn't have to contact Mailchimp
every time it wants to gather subscription data.

In other words, this should be enabled if possible. Otherwise, you may be using
inaccurate information in Drupal. It is also important to note that the webhook
doesn't just clear cached data, but actually updates the cached data.

*Note: You cannot test webhooks if developing locally, as the Mailchimp system
can't reach your local computer unless you enable a tunneling service like
ngrok.*
