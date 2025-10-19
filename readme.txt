=== Custom CSS for Elementor ===
Plugin Name: Custom CSS for Elementor
Version: 2.1.1
Author: Sayedul Sayem
Author URI: https://sayedulsayem.com/
Contributors: sayedulsayem, ikamal, gtarafdarr
Tags: elementor, css, custom css, responsive css, elementor addons
Requires at least: 5.0
Tested up to: 6.8
Requires PHP: 7.4
Stable tag: 2.1.1
License: GPL v3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

This plugin helps you push custom CSS in any native Elementor widget, solving style control limitations.

== Description ==

Custom CSS for Elementor is a handy tool for writing CSS codes within different devices like Desktops, Tablets & Mobile for Elementor Widgets. It will help you to not bother with writing media queries several times. Custom CSS for Elementor plugin comes with the default media query system.

See the plugin's GitHub repository [Custom CSS for Elementor](https://github.com/sayedulsayem/custom-css-for-elementor)

## 📢 Big Announcement -

After getting several requests we have added live preview mode while styling with CSS. Now you can see the reflection instantly. No hassle of continual preview checks in the front end. 

Enjoy styling with CSS without any hassle. 

### **Required Tools**

* Elementor Free Only


### 📘 **How to Use Custom CSS for Elementor**

First of all, it’s a plug-and-play tool. You will not get any setting page. So, after installing the Custom CSS for Elementor tool, just navigate to any Elementor Widget editing panel, then jump on the Advanced tab section; you will get a new accordion of Custom CSS for Elementor. Unfold the accordion, and you will get three different fields to write CSS codes for your widgets. If you want to show different designs for different devices, choose the device panels according to your needs. 

Direction: Edit any Elementor Widget > Advanced Tab > Custom Css for Elementor

Note: According to general CSS logic, if you write CSS for a bigger screen (Desktop), it will be automatically implemented on smaller devices chronologically. If you want to change anything for a smaller screen, you have to write different CSS for smaller screens. 

In our Custom CSS for Elementor addon, if you want to add global CSS, just write CSS code in the desktop/global panel, and the code will be automatically implied to the other screen. If you want to change the CSS for Tablets, you have to write different CSS in our Tablet CSS panel. And same goes for mobile devices. If you want to change the CSS for Mobile devices, write different CSS in the Mobile CSS panel of our tool. 

Don’t forget to check the Screenshots below to get a clear overview of the tool.


### ❓ **Why You Need This**

If you know the Elementor’s plenty of Dom creation issues, you will know the pain. But you can reduce so many bloated codes if you use simple CSS. But writing CSS for various devices, you need to be an expert on CSS as you have to deal with the Media Query every time. But our Custom CSS for Elementor addon will be the savior for you. We are handling the pain part of yours. Moreover, to add custom CSS, you don’t need to go to the Additional CSS of the theme customizer if you are using Elementor Free to manage your site as we build this tool for you. It’s just an advanced version of the Elementor Pro’s Default Custom CSS panel.


### 🔥 **What Are the Key Features of Elementor Custom CSS Plugin**

As the essential tool, we have shared with you. But in short, here are the other features of the custom CSS addon for Elementor,

* It’s a lightweight plugin that will use Elementor CSS files to enqueue custom CSS. Won’t generate any new CSS to reduce server requests.
* You can face lacking options during change style through style control, but our Custom CSS for Elementor Addon will allow you to write CSS to overcome these lackings.
* We prepared this tool by maintaining a World-class safety methodology. Also, we have run the malicious test, and it passed all the tests. 
* It works with any Elementor widget and other third-party Elementor addons widgets too.

### 💻 How to Change Breakpoints in WordPress
To modify breakpoints for tablet and mobile devices in WordPress, you can add custom code to your theme's `functions.php` file. Breakpoints determine when your website layout switches between different screen sizes, ensuring optimal display across devices.

#### Changing Tablet Breakpoint
To adjust the breakpoint for tablets, follow these steps:

1. Open your WordPress theme's `functions.php` file.
2. Add the following PHP code snippet:

```
add_filter( 'custom_css_for_elementor_breakpoints' , function( $default_breakpoints ) {
	$default_breakpoints['tablet'] = 768; // change this value
	return $default_breakpoints;
}, 20, 1);
```
3. Save the file.

#### Changing Mobile Breakpoint
To customize the breakpoint for mobile devices, use the following instructions:

1. Navigate to your theme's `functions.php` file.
2. Insert the following PHP code:

```
add_filter( 'custom_css_for_elementor_breakpoints' , function( $default_breakpoints ) {
	$default_breakpoints['mobile'] = 425; // change this value
	return $default_breakpoints;
}, 20, 1);
```
3. Save the changes.

By adjusting these breakpoints, you can fine-tune your website's responsiveness and ensure a seamless viewing experience across various devices.


### **PRIVACY POLICY**

We are not collecting any of your personal data or any kind of logs. So there will be no chance to violate your privacy through this plugin.


### **For Technical Support**

We all know nothing is perfect. But we all make everything perfect for everyone by sharing and caring. If you find any bug before giving us a negative rating, please report us [here](https://wordpress.org/support/plugin/custom-css-for-elementor/). We will definitely try to resolve your issue asap. Without your support, we cannot run this project so longer smoothly.

### **ABOUT THE MAKER**

I am [Sayedul Sayem](https://sayedulsayem.com/), a Bangladeshi full-stack WordPress developer and free and open source enthusiast. As Custom CSS for Elementor is an open-source project, you can encourage me by giving me a [5* rating](https://wordpress.org/support/plugin/custom-css-for-elementor/reviews/?filter=5). Nothing also, you can contact me on my [LinkedIn](https://www.linkedin.com/in/sayedulsayem/) for consultation or just to say hello. I love talking to new people. So don’t hesitate.


== Installation ==

1. Upload the plugin folder after extracting it to the “/wp-content/plugins/custom-css-for-elementor” directory. Alternatively, install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the ‘Plugins’ screen in WordPress
3. Now drag and drop an Elementor widget and go to the "Advanced Tab" of that widget. 
4. There will show up a new section called "Custom CSS for Elementor". Write your CSS to change your widget magically. Go to preview to see the changes.

For a more detailed explanation, check out the following [documentation](https://wordpress.org/support/article/managing-plugins/#installing-plugins).


== Frequently Asked Questions ==

= Will this plugin automatically work after activation? =

Yes. You don't need to do anything else other than activate the plugin.

= Is there any security thread after activating this plugin? =

No. This plugin is highly secure. It will sanitize user input so that the user can't face any scripting attack.


== Screenshots ==
1. An section called "Custom CSS for Elementor" will appear on ADVANCED_TAB in any widget/ section/ column/ flexbox container on the Elementor editor.
2. Write CSS for desktop.
3. Desktop preview with input CSS.
4. Write CSS for tablet.
5. Tablet preview with input CSS.
6. Write CSS for mobile.
7. Mobile preview with input CSS.

== Changelog ==

= 2.1.1 =
- Fix: Compatible with Elementor 3.32.x and Elementor Pro 3.32.x
- Fix: Compatible with WordPress 6.8.x

= 2.1.0 =
- New: Filter added for changing breakpoints.

= 2.0.0 =
- New: Live CSS generate on editor panel while writing CSS.

= 1.3.1 =
- Fix: Compatible with Elementor 3.16.x and Elementor Pro 3.16.x
- Fix: Compatible with WordPress 6.3.1

= 1.3.0 =
- New: Added support for the Flexbox Container.

= 1.2.0 =
- New: Support for the Column.
- New: Support for the Section.

= 1.1.0 =
- Fix: CSS input UI change and clean.
- Fix: CSS rules adjusted with normal CSS workflow.

= 1.0.0 (19-10-2021) =
* Initial release.