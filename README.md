# Description
WHMCS Bitcoin Payment plugin using BitBayar service. (https://bitbayar.com)

# Precondition
You must have a BitBayar merchant account to use this plugin. It's free and easy to [https://bitbayar.com/register](register).

# Installation
Extract these files into the WHMCS directory on your webserver (parent directory of modules/gateways).

# Configuration
1. Take a moment to ensure that you have set your store's domain and the WHMCS System URL under **whmcs/admin > Setup > General Settings**.
2. Get **API Token** in your BitPay merchant account:
  * Log into [https://bitbayar.com/register](https://bitbayar.com) with your account username/password.
  * Go to menu [https://bitbayar.com/setting](Setting & API)
  * On first box column, you will find **API TOKEN**
  * Select and copy the entire string for your API Token. It will look something like this: GD1FA2CC2A3357FDF45B84744FFF7102.
3. In the admin control panel, go to **Setup > Payment Gateways**, select **Bitcoin (BitBayar)** in the list of modules and click Activate.
4. Paste the API Key ID string that you created and copied from step 2.
5. Click **Save Changes**.

You're done!


# Usage
When a client chooses the BitBayar payment method, they will be presented with an invoice showing a button they will have to click on in order to pay their order. Upon requesting to pay their order, the system takes the client to a full-screen bitbayar.com invoice page where the client is presented with payment instructions. Once payment is received, a link is presented to the shopper that will return them to your website.

**NOTE:** Don't worry! a payment will automatically update your WHMCS store whether or not the customer returns to your website after they've paid the invoice.

In your WHMCS control panel, you can see the information associated with each order made via BitBayar by choosing **Orders > Pending Orders**. This screen will tell you whether payment has been received by the BitBayar servers. You can also view the details for any paid invoice inside your BitBayar merchant dashboard under the **Payments** page.


# Support
*Bitbayar Support*
* [https://bitbayar.com/support](Support)
* [https://bitbayar.com/dev](BitBayar Documentation)

*WHMCS Support:*
* [https://www.whmcs.com/](Homepage)
* [http://docs.whmcs.com/Main_Page](Documentation)
* [http://forum.whmcs.com/](SupportForums)


#License
http://opensource.org/licenses/MIT

#Author
## Teddy Fresnel
Website : [www.teddyfresnel.com](http://www.teddyfresnel.com)