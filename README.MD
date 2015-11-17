This is a Drupal 8 module for Brafton/ContentLead/Castleford clients to import content from their XML feed into their Drupal 8 site.

##Requirements

Drupal 8: version beta14+

PHP:
- DOMDocument
- cURL
- (Video) fOpen with "allow_url_fopen = true"

##Installation

1. Download .zip file from https://github.com/ContentLEAD/BraftonDrupal8Module/tree/development.
2. In the Drupal admin bar, click the "Extend" button.
3. Click the "Install new module" button (example.com/admin/modules/install).
4. Click the "Choose File" button and select the .zip file downloaded in step 1.
5. Click the "Install" button.
6. Assuming the installation was successful, click the "Enable newly added modules" link (example.com/admin/modules).
7. Scroll down or search for "Brafton Importer Module" and click the checkbox next to it.
8. Scroll to the bottom of the page and click the "Install" button.

##Basic Setup

1. In the Drupal admin bar, click the "Configuration" button.
2. Find the link for "Brafton Importer module settings" and click it. This brings you to the Brafton Importer config page (example.com/admin/config/content/brafton).

**Under "General Options"**
3. Turn "Master Importer Status" to "On".
4. Set your "API Root" to be either "Brafton", "ContentLead", or "Castleford".

**Under "Article Options"**
5. Turn "Article importer status" to "On".
6. Enter your  API key in the "API key" field.

**Under "Video Options" (if applicable)**
7. Turn "Video importer status" to "On".
8. Enter your public key in the "Public Key" field.
9. Enter your private key in the "Private Key" field.
10. Enter your feed number in the "Feed Number" field.

11. Scroll to the bottom and click the "Save configuration" button.

Now your site is set to import content every time the Drupal Cron runs (default is every 3 hours), which can be configured at example.com/admin/config/system/cron.

##Manual Control

Apart from running periodically (and automatically), you can also import articles and/or videos manually. At the Brafton Importer config page (example.com/admin/config/content/brafton), under "Manual Control", there is a button to manually import articles and a button to manually import videos. Running the importer manually does the exact same thing as when the importer runs on schedule. That is, the corresponding XML feed is checked and any articles are imported that do not already exist on the site.

##Category and Archives Views

Upon installation of the module, two Drupal "Views" are created: "Brafton Archive" and "Brafton Categories".

The views provide two Drupal "Blocks": "Brafton Archive Block" and "Brafton Categories".

The "Brafton Archive Block" is for linking to all categories in a sidebar.

The "Brafton Archive Block" is for linking to all month-year combinations in a sidebar.

##Configuration Options

###General Options

**Master importer status**: Allows the importer module to run periodically via the Drupal Cron.

**API Root**: The brand associated with the XML feed (Brafton, ContentLead, or Castleford).

**Brafton Categories**: For using article categorization from the XML feed.

**Overwrite any changes made to existing content**: Normally the importer module will pass over articles from the XML feed that already exist as Drupal articles. However if this option is checked, the importer will overwrite/update the existing Drupal articles with the corresponding XML information.

**Publish status**: Import Drupal articles as published nodes or unpublished nodes. Unpublished nodes will not display to the public.

###Article Options

**Article importer status**: Allows the importer module to import articles periodically via the Drupal Cron.

**API key**: This is your unique key provided by your Account Manager to access your XML Feed.

**Content author**: Choose the author (Drupal user) of imported articles. Select "Get author from article" to use the author name listed in the XML feed.

**Publish date**: XML articles come with 3 dates. Choose which one you want to use as the official article publish date.

###Video Options

**Video importer status**: Allows the importer module to import video articles periodically via the Drupal Cron.

**Public key**: Provided by your Account Manager.

**Private key**: Provided by your Account Manager.

**Feed Number**: The number of the feed under the client. Usually 0.

**Content author**: Choose the author (Drupal user) of imported video articles.

**Publish date**: XML articles come with 2 dates. Choose which one you want to use as the official article publish date.

**Atlantis JS switch**: Inserts link to javascript library into the head section of the site. Using this library changes the look of the videos and allows advanced video functionality like Call-to-Actions (CTAs) and social sharing.

**CTA switch**: Enables your videos to have Call-to-Actions (CTAs).

**Atlantis Pause CTA Text**: Text of link that appears upon video pause.

**Atlantis Pause Link**: URL of link that appears upon video pause.

**Pause Asset Gateway ID**: The form id associated with the Asset Gateway Account. Entering an Asset Gateway ID disables the pause link.

**Atlantis End CTA Title**: Title text that appears when video ends.

**Atlantis End CTA Subtitle**: Subtitle text that appears when video ends.

**Atlantis End CTA Link**: URL of link that appears when video ends.

**End Asset Gateway ID**: The form id associated with the Asset Gateway Account. Entering an Asset Gateway ID disables the end link.

**Atlantis End CTA Text**: Text of link that appears when video ends.

**Ending CTA Button Image**: Image to use instead of text for end link.

**Ending Background Image**: Image to display as background at end of video.

###Archive Uploads

**Article Archive File**: Browse your local computer for an XML file to import articles from.

###Error Reporting

Displays a log of any error or warning messages triggered by the importer. Note that all fatal errors are automatically reported to the Brafton servers.

**Debug mode**: While in debug mode, all errors and warnings will be logged locally. Outside of debug mode, only fatal errors are logged locally.

##Updating

Once the importer module is installed, you can update to newer version through the Drupal admin interface (example.com/admin/reports/updates/update).
