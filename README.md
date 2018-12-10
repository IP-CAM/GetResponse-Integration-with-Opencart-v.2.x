# GetResponse OpenCart Integration

Keep your list growing! Easily integrate your OpenCart-based e-commerce solution using this dedicated plugin. By installing the GetResponse module in your OpenCart server, you can start expanding your contact list right away. You can add contacts by:
 
- Importing your existing customers from OpenCart
- Using any web form that you create in GetResponse
- When people sign up for your e-commerce platform running on OpenCart

## Getting started

1. Download the GetResponse plugin ZIP file from GetResponse Connect. Save it at a location handy for uploading the file to your OpenCart server.
2. Transfer the plugin archive to your OpenCart site using SCP or FTP. Make sure to put it to the top level OpenCart directory.
3. Log into your OpenCart server using administrative rights.
4. Go to Extensions >> Modules. Use the OpenCart user interface to install the plugin.
5. Find GetResponse on the list and click the Install icon. When the installation is successful the server displays a message at the top of the Modules page.

You’re done. You can now configure how OpenCart connects with your GetResponse account.

## Usage

### How do I connect my GetResponse account with OpenCart?

1. Log into your GetResponse account.
2. Go to My account >> Account details >> API & OAuth.
3. Copy the API key.
4. Log into your OpenCart administrative panel and go to Extensions >> Modules page.
5. Find GetResponse on the list and click the Edit icon.
6. Enter your GetResponse API key and click Connect. When the connection is successful, the plugin downloads your account data. This includes information like the list of forms and campaigns. You can access your GetResponse campaigns, exit popups, and forms without leaving the OpenCart server.
7. Click Save to write the plugin settings.

### How do I allow visitors to subscribe using a specific web form?

You can display any form or exit pop-up you create in GetResponse. Simply enable the option in the GetResponse module configuration and select which form to display.

#### Enable GetResponse forms in OpenCart

1. Log into OpenCart as an administrator.
2. Go to GetResponse module settings.
3. Find the section Subscribe via a form.
4. Select Allow subscriptions via forms.
5. Select the form. Our integration automatically downloads all available forms and presents their names in a list. You can select the form from the list.
6. Click Save (find the button ant the top of the page) to confirm the changes.

#### Configure where to display the form

**Important**. You need to configure the form placement so your GetResponse form displays correctly and doesn’t break the site layout. Configure the GetResponse element position just like you configure any other OpenCart module. You can adjust all the available settings to control how the form or exit pop-up behaves.

1. Go to Design >> Layouts and select the layout name where you want to display a form or an exit pop-up. For example, if you want to show a form on the service homepage select Home and click the Edit icon.
2. Click Add module to start extending the layouts.
3. In the Module list, select GetResponse (it’s in the Featured section after you expand the list).
4. Select position and sorting number where you want the form to be visible. The sorting number allows you to select the order of elements on a specific page. If you want the form on top enter 0 (zero) as the sorting number.
5. Click Save icon to confirm layout changes and publish the form on your site.
That’s it. Your GetResponse form is now on your OpenCart e-commerce site.

### Can visitors subscribe at the time they register to my e-commerce platform?

Yes, you can allow visitors to subscribe to your newsletter when they create an account on your site. Enable this feature in GetResponse module configuration (disabled by default).

1. Log into OpenCart as an administrator.
2. Go to GetResponse module settings.
3. Find the section Subscribe via registration form.
4. Select Allow subscriptions when visitors register.
5. Choose a campaign for new subscribers to receive. Optionally, you can decide if the subscribers should receive autoresponder messages. Select Add to autoresponder sequence and then choose the day in autoresponder sequence.
6. Click Save to confirm the changes.

That’s it. From now on, your site registration form will include a checkbox encouraging users to subscribe.

### How do I export existing customer data from OpenCart to GetResponse?

You can export all the contacts you’ve already collected in OpenCart to GetResponse on demand. All you need to do is select the campaign where we should import the subscriber data.

1. Log into OpenCart as an administrator.
2. Go to GetResponse module settings.
3. Find Export customers.
4. Select the target campaign where you want to import your contacts.
5. Optionally, you can decide if the subscribers should start receiving autoresponder messages. Select Add to autoresponder sequence and then choose the day in autoresponder sequence.
6. Click Export. The contacts information goes straight to your GetResponse account. Note that existing contact data is updated when you perform the export.

## Support

If you have any trouble installing or working the plugin feel free to contact us by [phone, email or chat](https://support.getresponse.com/). You can also get more info on [GetResponse App Center](https://connect.getresponse.com/) page.

## Contributing

We love feature requests! Bug reports are very welcome as well. You can participate in this project by [reporting an issue](https://github.com/GetResponse/opencart/issues) or creating a [pull request](https://github.com/GetResponse/opencart/pulls). Please remind that all features and fixes must stick to platform coding standards.