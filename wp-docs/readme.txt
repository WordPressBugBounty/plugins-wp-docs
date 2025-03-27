=== WP Docs ===
Contributors: fahadmahmood, invoicepress
Tags: wp docs, memphis-documents-library, documents, library folders, directory
License: GPL2
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Requires at least: 3.5
Tested up to: 6.8
Stable tag: 2.2.8
Requires PHP: 7.0
A documents management tool for education portals.

== Description ==

* Author: [Fahad Mahmood](https://www.androidbubbles.com/contact)

* Project URI: <http://androidbubble.com/blog/wordpress/plugins/wp-docs>

* Demo URI: <https://nsvwaterplan.org/documents-library>

* License: GPL 3. See License below for copyright jots and titles.

WP Docs is a documents management plugin. You can manage user roles to restrict access to files, directory view, file types allowed for uploading. Nicely managed layouts with optional Bootstrap wrappers.

= Tags =
upload, download, library, files, breadcrumb

= How it works? =
[youtube https://youtu.be/k5bZqZ5dW30]


= WP Docs Insights =

* Breadcrumb bar is available for a better navigation.
* Front end refresh based files browser.
* Front end ajax based browsing.
* Document preview and thumbnails available for most file types.
* Multiple directory views are available, also with the shortcodes.
* Multiple instances are possible with the shortcodes, icons, list and details.
* Posts created for each new directory and your library items will be considered as file items using meta fields for post efficiently.
* The ability to create unlimited, edit and delete directories and sub-directories.
* A smooth navigation experience based on jQuery/Ajax instead of page refresh. This is a premium feature.
* Each folder can have a separate shortcode so you can use different pages for different directory listing and files inside.
* Folder icon and title size can be changed in pixels or percentage from settings page.



== Frequently Asked Questions ==
= How can I delete multiple files at once? =
[youtube https://youtu.be/gjDT0vxpmxQ]

= How can I copy/clone non-empty directory/files from one directory to another? =
[youtube https://youtu.be/nxfw1nMkSdg]

= How can I change directories background, text and hover colors? =
[youtube https://youtu.be/sygwux1752A]

= How can I change sort order by Filename or Post Title? =
[youtube https://youtu.be/bGhUaGtyVD8]

= How security level feature work?  =
Click on any directory and select a logged in user role as a security level. So automatically logged in and that required role will be implemented as dual security. In addition, all sub-directories will follow get the same security level.

= What if I accidentally turn back to FREE version from PRO?  =
Your data security is important for us. In case you got replaced the PRO version with a FREE one, all secured directories and content inside will remain secured and will work in the same way. The only missing part would be, new security level implementation and/or removal.

= How to manage files with this plugin?  =
You can create directories with sub-directories to manage files with this plugin. You can add a new folder with "New Folder" button and edit / delete files or folders with Edit / Delete buttons.

= How to implement different shortcodes on different sub-pages with different sub-folders? =
Create a new sub-folder / sub-directory, different shortcode will appear in shortcode section. so you can use different pages for different directory listing and files inside.

= What are the advanced features? =
[youtube https://youtu.be/cV-u3Iyt8kc]

= How users can download the files from front-end? =
[youtube https://youtu.be/g7mhlOCLhaw]

= Can files be uploaded from front-end? Who can upload files from front-end? =
Yes, Admin can allow user roles for uploading files. Allowed user roles can upload files from front-end. If admin will allow so logged in user can delete the files related to his account from front-end.

[youtube https://youtu.be/flFmqpJCwYk]

= What type of files user can upload? =
Allowed file types can be defined by admin and user can upload allowed file types only.

= Can logged-in user see all files? =
If "Show files uploaded by logged-in user only" is not checked then logged-in user can see all the files. If this box is checked, logged-in user can see only his uploaded files.
[youtube https://youtu.be/h5wDMgqT5Ys]

= How to import files from Memphis Document Library? =

On settings page, there is a button named Memphis Documents Library. Open it and click "Import From Memphis Documents Library". Confirm importing by clicking OK. All files will be imported from Memphis Documents Library.

[youtube https://youtu.be/nTFhOcJ2fNk]

= Is it safe to rollback import? =

Yes, it is safe to rollback import. When you will undo import, imported files from Memphis Documents Library will be deleted from WP Docs directory but still remain in Memphis Library.


= Uninstalling WP Docs =

When you uninstall the plugin, files will still remain in your WordPress library because these files are linked within these directories not physically moved within.

== Installation ==

From the WordPress plugin menu click on Add New and search for WP Docs

Instead of searching for a plugin you can directly upload the plugin zip file by clicking on Upload:

Use the browse button to select the plugin zip file that was downloaded, then click on Install Now. The plugin will be uploaded to your blog and installed. It can then be activated.

"WP Docs" is available in left admin menu under settings. 

== Screenshots ==

1. Settings Page - Customized
2. Settings Page - Parts
3. Delete file / folder or edit file / folder
4. Go back / add files to directory
5. List, details and large icons view
6. Different shortcodes for different directories / folders
7. Directory browsing facility
8. Manage directories
9. Thumbnails Display
10. WP Docs menu under settings
11. Security Level - User Roles Dropdown
12. Create a new folder
13. WP Responsive Tabs Compatbility - Documents inside tabs
14. Single click selection - When you enable front-end delete feature
15. Double click action - When you enable front-end delete feature
16. Order file by title, date and modified.
17. Settings Page - Full
18. Import files from Memphis directory.
19. WP Docs directory after importing files from Memphis directory.
20. File description instead of file name.

== Changelog ==
= 2.2.8 =
* Fix: Deleting unused folder in WP Docs. [Thanks to Jordan Wells / gclna.org]
= 2.2.5 =
* Fix: Imagick related exceptions added. [Thanks to James Oliver / Paluma]
= 2.2.2 =
* Fix: Discovered and reported this Cross Site Scripting (XSS) vulnerability in directory renaming functionality. [Thanks to b4orvn / Patchstack]
= 2.2.1 =
* Fix: $wpdb->prepare() for every database query that is not completely hard-coded and accepts user-supplied input to prevent SQL injection. [Thanks to Arkadiusz Hydzik / Peter Greaves / Wordfence]
= 2.1.8 =
* Notice: Function _load_textdomain_just_in_time was called incorrectly. Translation loading for the wp-docs domain was triggered too early. [27/11/2024][Thanks to Thomas]
= 2.1.7 =
* New: Help tab added so users can contact the plugin author for custom help in case they need it. [30/10/2024][Thanks to @damdam38]
= 2.1.5 =
* New: Custom file icon for PDF files. [08/05/2024][Thanks to Riccardo Catone]
= 2.1.4 =
* Fix:  LVT-tholv2k discovered and reported this Cross Site Scripting (XSS) vulnerability in WordPress WP Docs Plugin to Patchstack. [02/05/2024][Thanks to patchstack.com | Darius S. | LVT-tholv2k]
= 2.1.3 =
* New: Multi-select and bulk delete option. [25/04/2024][Thanks to Cory Bonallo]
= 2.1.2 =
* New: Custom file icon for PDF files. [24/04/2024][Thanks to Riccardo Catone]
= 2.1.1 =
* Fix: Problem with security level. [17/04/2024][Thanks to Erblin Shehu]
= 2.1.0 =
* Fix: The renaming comes into effect only if we refresh the page. [14/02/2024][Thanks to nikhildev]
= 2.0.9 =
* Fix: Download button functionality tested in multi-site environment. [03/02/2024][Thanks to Cory Bonallo]
= 2.0.8 =
* Fix: Download button functionality glitch. [01/02/2024][Thanks to Cory Bonallo]
= 2.0.7 =
* New: Shortcut can be created of a link as well. [31/01/2024][Thanks to Michael Lüers]
= 2.0.6 =
* Fix: Folder names with an ampersand "&" in the name due to jQuery.html() will not bother the administrator anymore. [25/01/2024][Thanks to Cory Bonallo]
= 2.0.5 =
* Revised: Any file or folder names with an ampersand "&" in the name will not delete or open. The & character is stored as the HTML equivalent. [23/01/2024]
= 2.0.4 =
* Fix: Any file or folder names with an ampersand "&" in the name will not delete or open. The & character is stored as the HTML equivalent. [Thanks to @cbonallo][21/01/2024]
= 2.0.3 =
* Fix: filesize() related warning muted. [Thanks to @cbonallo][02/08/2023]
= 2.0.2 =
* New: Ajax based deep search option introduced for nth child level search for directories and files with just one input field. [Thanks to Eleonora Gardini & Riccardo Catone][07/07/2023]
= 2.0.1 =
* New: Scroll to the navigated directory ID div. [Thanks to Taylor Bacigalupo][23/05/2023]
= 2.0.0 =
* Fix: XSS vulnerability removed on admin settings page through the directory name in the URL. [Thanks to Darius Sveikauskas][02/05/2023]
= 1.9.9 =
* Fix: Nonce ensured while creating new directories, updating and deleting existing directories. [Thanks to Darius Sveikauskas][18/04/2023]
= 1.9.8 =
* Fix: A colspan attribute corrected on files list view. [Thanks to Henri Paturel][23/11/2022]
= 1.9.7 =
* Fix: Document title cleanup. [Thanks to bharathfury939][09/10/2022]
= 1.9.6 =
* Fix: Call to a member function ab_io_display() on null. [Thanks to estreet706][15/09/2022]
= 1.9.5 =
* Fix: Memphis Documents Library downward compatibility revised. [Thanks to Oscar Polanco][12/09/2022]
= 1.9.4 =
* Fix: Maria DB Error (You have an error in your SQL syntax). [Thanks to Erivelto][28/07/2022]
= 1.9.3 =
* Fix: Detail view sortable columns. [Thanks to Lucas][16/06/2022]
= 1.9.2 =
* Fix: List view and detail view, icon float-left fix. [Thanks to David Waiengnier][11/06/2022]
= 1.9.1 =
* New: Copy/Clone directory/files feature has been refined. [Thanks to Ana Karina Rodríguez Pérez][13/05/2022]
= 1.9.0 =
* New: Copy/Clone directory/files feature has been added. [Thanks to Ana Karina Rodríguez Pérez][12/05/2022]
= 1.8.9 =
* Order by post_title for the directories list, revised. [Thanks to Henry Delsol][11/04/2022]
= 1.8.8 =
* Order by post_title for the files list, revised. [Thanks to Henry Delsol][06/04/2022]
= 1.8.7 =
* File description instead of file name. [Thanks to David Waiengnier][25/03/2022]
= 1.8.6 =
* Multiple directories shortcode refined. [Thanks to milesthorne][12/02/2022]
= 1.8.5 =
* Multiple directories shortcode introduced. [Thanks to milesthorne][12/02/2022]
= 1.8.4 =
* Delete from front-end managed with logged in and logged out CSS classes. [Thanks to Zachjjackson]
= 1.8.3 =
* Delete from front-end should not be visible to relevant user role when logged in. [Thanks to Zachjjackson]
= 1.8.2 =
* Delete from front-end should not be visible if user is not logged in. [Thanks to Zachjjackson]
= 1.8.1 =
* Folder as a shortcut, new feature added. [Thanks to itsmir & Roi Webreach]
= 1.8.0 =
* Tools tab added with URL translator. [Thanks to Ana Karina Rodríguez Pérez]
= 1.7.9 =
* User experience improved, file will open with single click. [Thanks to Andrew Narunsky]
= 1.7.8 =
* Directory specific settings introduced. [Thanks to Daniel Hahn / WZLforum gGmbH Kirstin Marso]
= 1.7.7 =
* Memphis Documents Library compatibility revised.
= 1.7.6 =
* Memphis Documents Library compatibility added.
= 1.7.5 =
* List documents with their title instead of their name. [Thanks to alopezosa and simisb]
= 1.7.4 =
* Premium shortcode attributes displayed on settings page in FREE Version as well. [Thanks to Bryan Earl]
= 1.7.3 =
* Double click file opening on file list view fixed. [Thanks to Mairie de Charols]
= 1.7.2 =
* Sortable columns functionality added. [Thanks to Team Ibulb Work & GERMAN ZARZA CARO]
= 1.7.1 =
* Playstore App related scripts improved. [Thanks to Team Ibulb Work]
= 1.7.0 =
* Add files link revised. [Thanks to Team Ibulb Work]
= 1.6.9 =
* Add files icon initialized with 2 seconds delay. [Thanks to @sannea and @ronaldz]
= 1.6.8 =
* Borders can be removed, an optional feature added. [Thanks to borake197]
= 1.6.7 =
* PHP notice for Spanish language - Fixed. [Thanks to Abu Usman]
= 1.6.6 =
* Bootstrap class dropdown-menu conflict resolved with WP theme oceanwp. [Thanks to grg26450]
= 1.6.5 =
* Updating capabilities from Administrator to Editor for back-end. [Thanks to borake197]
= 1.6.4 =
* FREE version, details view, click event handled for files opening. [Thanks to Bien & Team Ibulb Work]
= 1.6.3 =
* Single click and double click issue resolved. [Thanks to Fusion Graphics Phuket]
= 1.6.2 =
* WP Responsive Tabs compatibility ensured. [Thanks to Antony]
= 1.6.1 =
* Unknown filetype uploaded and was appearing uselessly - Fixed. [Thanks to borake197]
= 1.6.0 =
* FAQ's updated. [Thanks to Abu Usman]
= 1.5.9 =
* Security checklist ensured. [Thanks to borake197]
= 1.5.8 =
* Fontawesome CSS and webfonts updated.
= 1.5.7 =
* Fontawesome added to CSS and JS folders. [Thanks to pererikolsen]
= 1.5.6 =
* Numerous features added including view parameter in shortcodes. [Thanks to Stef & Sonia]
= 1.5.5 =
* Ajax based shortcodes will keep the settings unique while loading content. [Thanks to Antony]
= 1.5.4 =
* word-wrap: break-word, added for titles. [Thanks to Wesker HU]
= 1.5.3 =
* Reset settings button added with red color. [Thanks to Wesker HU]
= 1.5.2 =
* Android App released. [Thanks to Team AndroidBubbles]
= 1.5.1 =
* Instead of using sessions, we are using database. [Thanks to therustyfox & Team Ibulb Work]
= 1.5.0 =
* session_start() muted in this version. [Thanks to therustyfox & Team Ibulb Work]
= 1.4.9 =
* Filter box feature added. [Thanks to Interpolat Solutions]
= 1.4.8 =
* UI improved.
= 1.4.7 =
* Download directory option added. [Thanks to Team Ibulb Work]
= 1.4.6 =
* Updated date and time format. [Thanks to Jürgen]
= 1.4.5 =
* Updated assets.
= 1.4.4 =
* Security level introduced. [Thanks to therustyfox]
= 1.4.3 =
* User experience improved. [Thanks to Andrew Narunsky]
= 1.4.2 =
* WP Docs (1.4.1) User experience improved. [Thanks to dergrosz]
= 1.4.1 =
* WP Docs (1.4.0) sort documents by Title admin side and breadcrumbs - fixed. [Thanks to reason8 & Andrew Narunsky]
= 1.4.0 =
* WP Docs (1.3.9) sort documents by Title - fixed. [Thanks to reason8]
= 1.3.9 =
* Fixed files uploading error. [Thanks to josephtobi53 & dondiegoesparza09]
= 1.3.8 =
* Video tutorials added. [Thanks to Abu Usman]
= 1.3.7 =
* Thumbnails toggle provided on settings page. [Thanks to Abu Usman]
= 1.3.6 =
* Front-end files upload feature updated.
= 1.3.5 =
* Front-end files upload feature added. [Thanks to asabo & nize]
= 1.3.4 =
* A few minor CSS corrections.
= 1.3.3 =
* Bootstrap toggle provided. [Thanks to therustyfox]
= 1.3.2 =
* Ajax Based Directory Navigation refined.
= 1.3.1 =
* Ajax Based Directory Navigation added. [Thanks to Ibulb Work Team]
= 1.3.0 =
* Assets refined. [Thanks to Abu Usman]
= 1.2.9 =
* Shortcodes added. [Thanks to nize]
= 1.2.8 =
* Delete functionality improved with long filename. [Thanks to Lubomir]
= 1.2.7 =
* Delete functionality revised. [Thanks to boneceklp]
= 1.2.6 =
* Screenshots updated.
= 1.2.5 =
* Bootstrap based front-end reviewed. [Thanks to Abu Usman]
= 1.2.4 =
* Bootstrap based front-end revised. [Thanks to boneceklp]
= 1.2.3 =
* Bootstrap based front-end released. [Thanks to Team Ibulb Work]
= 1.2.2 =
* Languages added. [Thanks to Abu Usman]
= 1.2.1 =
* Multiple uploads refined.
= 1.2.0 =
* Legacy option removed, please don't update this version if you're using legacy.
= 1.1.9 =
* Fixed: WordPress Plugin Security Vulnerability / Missing Validation on TLS Connections
= 1.1.8 =
* New functionality added with downward compatibility.
= 1.1.6 =
* A few important updates.
= 1.1.5 =
* Sanitized input and fixed direct file access issues.
= 1.1.4 =
* Fixed multiple level child directory issue in Pro version.
= 1.1.3 =
* Fixed 2nd level child directory addition problem. [Thanks to Joe & Mike]
= 1.1.1 =
* Fixed parent directory related files display.
= 1.1.0 =
* Releasing with complex directory structure and improved jQuery files browser on front-end.
= 1.0 =
* Initial Release of WP Docs

== Upgrade Notice ==
= 2.2.8 =
Fix: Deleting unused folder in WP Docs.
= 2.2.5 =
Fix: Imagick related exceptions added.
= 2.2.2 =
Fix: Discovered and reported this Cross Site Scripting (XSS) vulnerability in directory renaming functionality.
= 2.2.1 =
Fix: $wpdb->prepare() for every database query that is not completely hard-coded and accepts user-supplied input to prevent SQL injection.
= 2.1.8 =
Notice: Function _load_textdomain_just_in_time was called incorrectly. Translation loading for the wp-docs domain was triggered too early.
= 2.1.7 =
New: Help tab added so users can contact the plugin author for custom help in case they need it.
= 2.1.5 =
New: Custom file icon for PDF files.
= 2.1.4 =
Fix:  LVT-tholv2k discovered and reported this Cross Site Scripting (XSS) vulnerability in WordPress WP Docs Plugin to Patchstack.
= 2.1.3 =
New: Multi-select and bulk delete option.
= 2.1.2 =
New: Custom file icon for PDF files.
= 2.1.1 =
Fix: Problem with security level.
= 2.1.0 =
Fix: The renaming comes into effect only if we refresh the page.
= 2.0.9 =
Fix: Download button functionality tested in multi-site environment.
= 2.0.8 =
Fix: Download button functionality glitch.
= 2.0.7 =
New: Shortcut can be created of a link as well.
= 2.0.6 =
Fix: Folder names with an ampersand "&" in the name due to jQuery.html() will not bother the administrator anymore.
= 2.0.5 =
Revised: Any file or folder names with an ampersand "&" in the name will not delete or open. The & character is stored as the HTML equivalent.
= 2.0.4 =
Fix: Any file or folder names with an ampersand "&" in the name will not delete or open. The & character is stored as the HTML equivalent.
= 2.0.3 =
Fix: filesize() related warning muted.
= 2.0.2 =
New: Ajax based deep search option introduced for nth child level search for directories and files with just one input field.
= 2.0.1 =
New: Scroll to the navigated directory ID div.
= 2.0.0 =
Fix: XSS vulnerability removed on admin settings page through the directory name in the URL.
= 1.9.9 =
Fix: Nonce ensured while creating new directories.
= 1.9.8 =
Fix: A colspan attribute corrected on files list view.
= 1.9.7 =
Fix: Document title cleanup.
= 1.9.6 =
Fix: Call to a member function ab_io_display() on null.
= 1.9.5 =
Fix: Memphis Documents Library downward compatibility revised.
= 1.9.4 =
Fix: Maria DB Error (You have an error in your SQL syntax). 
= 1.9.3 =
Fix: Detail view sortable columns.
= 1.9.2 =
Fix: List view a detail view, icon float-left fix.
= 1.9.1 =
Copy/Clone directory/files feature has been refined.
= 1.9.0 =
Copy/Clone directory/files feature has been added.
= 1.8.9 =
Order by post_title for the directories list, revised.
= 1.8.8 =
Order by post_title for the files list, revised.
= 1.8.7 =
File description instead of file name.
= 1.8.6 =
Multiple directories shortcode refined.
= 1.8.5 =
Multiple directories shortcode introduced.
= 1.8.4 =
Delete from front-end managed with logged in and logged out CSS classes.
= 1.8.3 =
Delete from front-end should not be visible to relevant user role when logged in.
= 1.8.2 =
Delete from front-end should not be visible if user is not logged in.
= 1.8.1 =
Folder as a shortcut, new feature added.
= 1.8.0 =
Tools tab added with URL translator.
= 1.7.9 =
User experience improved.
= 1.7.8 =
Directory specific settings introduced.
= 1.7.7 =
Memphis Documents Library compatibility revised.
= 1.7.6 =
Memphis Documents Library compatibility added.
= 1.7.5 =
List documents with their title instead of their name.
= 1.7.4 =
Premium shortcode attributes displayed on settings page in FREE Version as well.
= 1.7.3 =
Double click file opening on file list view fixed.
= 1.7.2 =
Sortable columns functionality added.
= 1.7.1 =
Playstore App related scripts improved.
= 1.7.0 =
Add files link revised.
= 1.6.9 =
Add files icon initialized with 2 seconds delay.
= 1.6.8 =
Borders can be removed, an optional feature added.
= 1.6.7 =
PHP notice for Spanish language - Fixed.
= 1.6.6 =
Bootstrap class dropdown-menu conflict resolved with WP theme oceanwp. 
= 1.6.5 =
Updating capabilities from Administrator to Editor for back-end.
= 1.6.4 =
FREE version, details view, click event handled for files opening.
= 1.6.3 =
Single click and double click issue resolved.
= 1.6.2 =
WP Responsive Tabs compatibility ensured.
= 1.6.1 =
Unknown filetype uploaded and was appearing uselessly - Fixed. 
= 1.6.0 =
FAQ's updated.
= 1.5.9 =
Security checklist ensured.
= 1.5.8 =
Fontawesome CSS and webfonts updated.
= 1.5.7 =
Fontawesome added to CSS and JS folders.
= 1.5.6 =
Numerous features added including view parameter in shortcodes. [Thanks to Stef & Sonia]
= 1.5.5 =
Ajax based shortcodes will keep the settings unique while loading content. [Thanks to Antony
= 1.5.4 =
word-wrap: break-word, added for titles.
= 1.5.3 =
Reset settings button added with red color.
= 1.5.2 =
Android App released. 
= 1.5.1 =
Instead of using sessions, we are using database.
= 1.5.0 =
session_start() muted in this version.
= 1.4.9 =
Filter box feature added.
= 1.4.8 =
UI improved.
= 1.4.7 =
Download directory option added.
= 1.4.6 =
Updated date and time format.
= 1.4.5 =
Updated assets.
= 1.4.4 =
Security level introduced.
= 1.4.3 =
User experience improved.
= 1.4.2 =
WP Docs (1.4.1) User experience improved.
= 1.4.1 =
WP Docs (1.4.0) sort documents by Title admin side and breadcrumbs - fixed.
= 1.4.0 =
Docs (1.3.9) sort documents by Title - fixed.
= 1.3.9 =
Fixed files uploading error.
= 1.3.8 =
Video tutorials added.
= 1.3.7 =
Thumbnails toggle provided on settings page.
= 1.3.6 =
Front-end files upload feature updated.
= 1.3.5 =
Front-end files upload feature added.
= 1.3.4 =
A few minor CSS corrections.
= 1.3.3 =
Bootstrap toggle provided.
= 1.3.2 =
Ajax Based Directory Navigation refined.
= 1.3.1 =
Ajax Based Directory Navigation added.
= 1.3.0 =
Assets refined.
= 1.2.9 =
Shortcodes added.
= 1.2.8 =
Delete functionality improved with long filename.
= 1.2.7 =
Delete functionality revised & jQuery added as a dependency.
= 1.2.6 =
Screenshots updated.
= 1.2.5 =
Bootstrap based front-end reviewed.
= 1.2.4 =
Bootstrap based front-end revised.
= 1.2.3 =
Bootstrap based front-end released.
= 1.2.2 =
Languages added.
= 1.2.1 =
Multiple uploads refined.
= 1.2.0 =
Legacy option removed, please don't update this version if you're using legacy.
= 1.1.9 =
Fixed: WordPress Plugin Security Vulnerability / Missing Validation on TLS Connections
= 1.1.8 =
New functionality added with downward compatibility.
= 1.1.6 =
A few important updates.
= 1.1.5 =
Sanitized input and fixed direct file access issues.
= 1.1.4 =
Fixed multiple level child directory issue in Pro version.
= 1.1.3 =
Fixed 2nd level child directory addition problem.
= 1.1.1 =
Fixed parent directory related files display.
= 1.1.0 =
Releasing with complex directory structure and improved jQuery files browser on front-end.
= 1.0 =
Initial Release of WP Docs

== License ==
This WordPress Plugin is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version. This free software is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details. You should have received a copy of the GNU General Public License along with this software. If not, see http://www.gnu.org/licenses/gpl-2.0.html.