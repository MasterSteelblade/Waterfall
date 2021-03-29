# Translators Guide

## Getting started

There are two possible types of translation you might do - creating a new translation, or updating an existing one.

### New translations

Creating a translation is pretty easy. In the `lang` folder, (the one this readme is in), there's a `lang_en.ini` file. This contains the base English locale, and can serve as a base for other translations. This will be the file you base your new translation on. 

The file is formatted as a regular `ini` type. This means there's sections, keys, and values. They work sort of like this:

```ini
[section]
key = "value"
another_key = "another value"

[another_section]
key = "value"
```

Keys can be duplicated as long as they're in a different section. Section names can't be duplicated. Keys have spaces replaced with underscores, and values should be quoted. 

**Important:** Don't delete or change any key or section names! The code needs them to find the correct translation. Only change values. 

Languages have a two character locale code - for example, English is `en`, Japanese is `jp`, Spanish is `es`. Some languages have a second locale code - for example, `en-us` and `en-gb`- but the code only currently checks for the first part. Name your new file `lang_xx.ini`, where `xx` is the locale code ffor the language you're translating to. You can find a list of codes [here](https://www.science.co.il/language/Locale-codes.php).

Make your way through the file, and when you're done, submit a pull request in accordance with standard git guidelines.

### Updating Translations

As new features get added, new strings might be localisable. When this happens, they'll be added to existing language files in English. If you notice one, you just need to change that line and make a pull request, same as above.

### Committing your work

There are two ways to commit your work. Both require a GitHub account. If you're familiar with Git already, you'll probably know about forks and pull requests already - just follow the usual procedure. 

If you don't have a GitHub account, make one! Then, fork the repository. On the page for the project, there'll be a "Fork" button in the top left - once you press it, go through the process and then you'll have a copy of Waterfall's code ready to work with. 

You can think of a repository fork as a fork in the road - it's where a path splits into two directions. Later on, they can merge again, or continue on their own path. In your fork, make the changes - If you're in the `lang` folder, you'll see an "Add file" option, which you can use to make your file. Name it lang_xx.ini (where xx is the locale code),
and copy paste the contents of lang_en.ini in there. Make your changes, and save. 

Now you're ready to make a pull request! Hit the pull request tab, and press "new pull request". Hit the "compare across forks" link. The `base repository` setting should be set to `MasterSteelblade/Waterfall`, with the `base` set to `main`. The `head repository` should be `YourUsername/Waterfall` and `compare` set to whatever branch you were working in (most likely main). 

It should show you the file changes, and if you're making a new translation, there'll be one file with a lot of green lines here. Hit Create Pull Request and follow the process - and you're done! Your work will be reviewed and merged into the site's code once it's been checked. 

### Pull Request Checks

When you submit your pull request, it'll do some tests. The one you need to worry about is the Language File Checks. If this fails, it means a translation is missing a key or section that's present in the English file. 

### `vsprintf`

This section is slightly technical, but it's used in the translation files so it's important you understand it.

`vsprintf` is a function in PHP for producing formatted strings including a variable - such as names, where you can't know what it'll be at the time you write it. The localisation code uses this to format certain things. An example of this is the permissions page title. 

```ini
[permissions]
title = "Group Blog Permissions for <strong>%s</strong> on <strong>%s</strong>"
```

You'll notice that inside the `<strong>` tags, we've put %s as a placeholder. This gets replaced by a given input, in order. There are two here, so if we look at the part where this is called:

```php
echo L::permissions_title($permittedBlog->blogName, $blog->blogName); 
```

There are two things being passed in. The blog you're adjusting permissions for, and the blog you're doing it on. If we assign an arbitrary value to each of these - staff and staff-member - we cna interpret it as it being the following...

```php
echo L::permissions_title('staff-member', 'staff')
```

... which then gets interpreted to the following output string:

```html
Group Blog Permissions for <strong>staff-member</strong> on <strong>staff</strong>
```

Simply pass the parameters in sequence, and the code handles the rest. `vsprintf` takes multiple options. In the above example, we used `%s` to denote a string, but you can use different things to tell
it to treat the inputs a little differently. For example, `%d` treats it as a signed integer. The full list of flags can be found on the [`vsprintf` function page](https://www.php.net/manual/en/function.vsprintf.php) if needed, but %s is the most likely one you'll be using. PHP tries to wrangle the input to a suitable type, but as a general rule, use whichever flag is used in the English locale file for the string you're translating and it'll probably work.

## Technical Notes
For those of you wanting to understand more about how things work, this is the section for you. The `vsprintf` section is likely to be of interest to everyone as it's used in the translations files, but the rest is optional reading.