---
title: Tagging rules
weight: 5
---

If you want to automatically assign a tag to new articles, this part of
the configuration is for you.

## What does « tagging rules » mean?

They are rules used by wallabag to automatically tag new entries. Each
time a new entry is added, all the tagging rules will be used to add the
tags you configured, thus saving you the trouble to manually classify
your entries.

## How do I use them?

Let assume you want to tag new entries as *« short reading »* when the
reading time is inferior to 3 minutes. In that case, you should put «
readingTime &lt;= 3 » in the **Rule** field and *« short reading »* in
the **Tags** field. Several tags can added simultaneously by separating
them by a comma: *« short reading, must read »*. Complex rules can be
written by using predefined operators: if *« readingTime &gt;= 5 AND
domainName = "www.php.net" »* then tag as *« long reading, php »*.

## Which variables and operators can I use to write rules?

The following variables and operators can be used to create tagging
rules (be careful, for some values, you need to add quotes, for example
`language = "en"`):

  Variable      | Meaning                                          
  ------------- | -------------------
  title         | Title of the entry                               
  url           | URL of the entry                                 
  isArchived    | Whether the entry is archived or not             
  isStarred     | Whether the entry is starred or not              
  content       | The entry's content                              
  language      | The entry's language                             
  mimetype      | The entry's mime-type                            
  readingTime   | The estimated entry's reading time, in minutes   
  domainName    | The domain name of the entry                     


  Operator     | Meaning
  ------------- | -------------
  &lt;=         | Less than…
  &lt;         | Strictly less than…
  =&gt;        | Greater than…
  &gt;         | Strictly greater than…
  =            | Equal to…
  !=           | Not equal to…
  OR           | One rule or another
  AND          | One rule and another
  matches      | Tests that a subject is matches a search (case-insensitive). Example: title matches "football"
  notmatches   | Tests that a subject is not matches a search (case-insensitive). Example: title notmatches "football"
