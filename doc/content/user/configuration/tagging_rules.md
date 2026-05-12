---
title: Tagging rules
weight: 5
---

If you want to automatically assign a tag to new articles, this part of
the configuration is for you.

## What are tagging rules?

These are rules used by wallabag to automatically tag new entries. Each time a
new entry is added, all the tagging rules will be processed to add the tags
that would match, thus saving you the trouble of manually classifying your
entries.

## How to use them?

Let assume you want to tag new entries as *« short reading »* when the
reading time is 3 minutes or less. In that case, you would put
`readingTime <= 3` in the **Rule** field and `short reading` in
the **Tags** field.

You can add several tags using a given rule by separating
them with a comma, e.g. `short reading, must read`.

Several operators are available in order to build more complex rules. For
example if you want to tag any article from `www.php.net` that has a reading time
of 5 minutes or more, you could use the rule `readingTime >= 5 AND domainName =
"www.php.net"`.

The variables and operators available for the tagging rules are listed below.

Please note that text must be quoted, e.g. `language = "en"`.

### Available Variables

  Variable      | Meaning
  ------------- | -------------------
  `title`       | Title of the entry
  `url`         | URL of the entry
  `isArchived`  | Whether the entry is archived or not
  `isStarred`   | Whether the entry is starred or not
  `content`     | The entry's content
  `language`    | The entry's language
  `mimetype`    | The entry's mime-type
  `readingTime` | The estimated entry's reading time, in minutes
  `domainName`  | The domain name of the entry

### Available Operators

  Operator     | Meaning
  ------------ | -------------
  `<=`         | Less than or equal to…
  `<`          | Less than…
  ̀`=>`         | Greater than or equal to…
  `>`          | Greater than…
  `=`          | Equal to…
  `!=`         | Not equal to…
  `OR`         | One rule or another
  `AND`        | One rule and another
  `matches`    | Tests that a subject is matching a pattern (_case-insensitive_), e.g. `title matches "football"`
  `notmatches` | Tests that a subject is not matching a pattern (_case-insensitive_), e.g. `title notmatches "football"`
