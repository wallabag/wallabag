---
title: URL list
weight: 8
---

If the service you are leaving isn't supported by wallabag, or if you
just have a bunch of links to save, you can import a plain text file
containing one URL per line.

## Prepare your file

Write each URL on its own line:

```
# Articles to read later
https://www.example.org/an-interesting-article
https://blog.example.com/2024/01/another-one

https://news.example.net/story?id=42
```

-   Only web addresses starting with `http://` or `https://` are
    imported, any other line is ignored.
-   Empty lines and lines starting with `#` are ignored, so you can
    use them to organize your file.
-   A URL which is listed several times, or which is already saved in
    your wallabag, is only imported once.

## Import your data into wallabag

Click on `Import` link in the menu, on `Import contents` in URL list
section and then select your text file and upload it.

The content of each article is fetched from its website, so the import
can be a demanding process for your server.

You can also import such a file using the [command-line
interface]({{< relref "/user/import/#import-via-command-line-interface-cli" >}})
with the `--importer=url_list` option.
