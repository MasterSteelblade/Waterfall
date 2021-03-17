# Translators Guide

## Welcome


## Getting started




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

