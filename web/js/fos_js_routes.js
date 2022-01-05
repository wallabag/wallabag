fos.Router.setData({
  base_url: '',
  routes: {
    import_chrome: {
      tokens: [['text', '/import/chrome']], defaults: [], requirements: [], hosttokens: [],
    },
    import_firefox: {
      tokens: [['text', '/import/firefox']], defaults: [], requirements: [], hosttokens: [],
    },
    import: {
      tokens: [['text', '/import/']], defaults: [], requirements: [], hosttokens: [],
    },
    import_instapaper: {
      tokens: [['text', '/import/instapaper']], defaults: [], requirements: [], hosttokens: [],
    },
    import_pinboard: {
      tokens: [['text', '/import/pinboard']], defaults: [], requirements: [], hosttokens: [],
    },
    import_pocket: {
      tokens: [['text', '/import/pocket']], defaults: [], requirements: [], hosttokens: [],
    },
    import_pocket_auth: {
      tokens: [['text', '/import/pocket/auth']], defaults: [], requirements: [], hosttokens: [],
    },
    import_pocket_callback: {
      tokens: [['text', '/import/pocket/callback']], defaults: [], requirements: [], hosttokens: [],
    },
    import_readability: {
      tokens: [['text', '/import/readability']], defaults: [], requirements: [], hosttokens: [],
    },
    import_wallabag_v1: {
      tokens: [['text', '/import/wallabag-v1']], defaults: [], requirements: [], hosttokens: [],
    },
    import_wallabag_v2: {
      tokens: [['text', '/import/wallabag-v2']], defaults: [], requirements: [], hosttokens: [],
    },
    user_new: {
      tokens: [['text', '/users/new']], defaults: [], requirements: [], hosttokens: [],
    },
    developer: {
      tokens: [['text', '/developer']], defaults: [], requirements: [], hosttokens: [],
    },
    developer_create_client: {
      tokens: [['text', '/developer/client/create']], defaults: [], requirements: [], hosttokens: [],
    },
    developer_delete_client: {
      tokens: [['variable', '/', '\\d+', 'id'], ['text', '/developer/client/delete']], defaults: [], requirements: { id: '\\d+' }, hosttokens: [],
    },
    developer_howto_firstapp: {
      tokens: [['text', '/developer/howto/first-app']], defaults: [], requirements: [], hosttokens: [],
    },
    config: {
      tokens: [['text', '/config']], defaults: [], requirements: [], hosttokens: [],
    },
    delete_tagging_rule: {
      tokens: [['variable', '/', '\\d+', 'id'], ['text', '/tagging-rule/delete']], defaults: [], requirements: { id: '\\d+' }, hosttokens: [],
    },
    edit_tagging_rule: {
      tokens: [['variable', '/', '\\d+', 'id'], ['text', '/tagging-rule/edit']], defaults: [], requirements: { id: '\\d+' }, hosttokens: [],
    },
    config_reset: {
      tokens: [['variable', '/', '[^/]++', 'type'], ['text', '/reset']], defaults: [], requirements: { id: 'annotations|tags|entries' }, hosttokens: [],
    },
    new_entry: {
      tokens: [['text', '/new-entry']], defaults: [], requirements: [], hosttokens: [],
    },
    new: {
      tokens: [['text', '/new']], defaults: [], requirements: [], hosttokens: [],
    },
    all: {
      tokens: [['variable', '/', '[^/]++', 'page'], ['text', '/all/list']], defaults: { page: '1' }, requirements: [], hosttokens: [],
    },
    archive: {
      tokens: [['variable', '/', '[^/]++', 'page'], ['text', '/archive/list']], defaults: { page: '1' }, requirements: [], hosttokens: [],
    },
    starred: {
      tokens: [['variable', '/', '[^/]++', 'page'], ['text', '/starred/list']], defaults: { page: '1' }, requirements: [], hosttokens: [],
    },
    archive_entry: {
      tokens: [['variable', '/', '\\d+', 'id'], ['text', '/archive']], defaults: [], requirements: { id: '\\d+' }, hosttokens: [],
    },
    untagged: {
      tokens: [['variable', '/', '[^/]++', 'page'], ['text', '/untagged/list']], defaults: { page: '1' }, requirements: [], hosttokens: [],
    },
    archive_rss: {
      tokens: [['text', '/archive.xml'], ['variable', '/', '[^/]++', 'token'], ['variable', '/', '[^/]++', 'username']], defaults: [], requirements: [], hosttokens: [],
    },
    starred_rss: {
      tokens: [['text', '/starred.xml'], ['variable', '/', '[^/]++', 'token'], ['variable', '/', '[^/]++', 'username']], defaults: [], requirements: [], hosttokens: [],
    },
    howto: {
      tokens: [['text', '/howto']], defaults: [], requirements: [], hosttokens: [],
    },
    new_tag: {
      tokens: [['variable', '/', '\\d+', 'entry'], ['text', '/new-tag']], defaults: [], requirements: { entry: '\\d+' }, hosttokens: [],
    },
    remove_tag: {
      tokens: [['variable', '/', '\\d+', 'tag'], ['variable', '/', '\\d+', 'entry'], ['text', '/remove-tag']], defaults: [], requirements: { entry: '\\d+', tag: '\\d+' }, hosttokens: [],
    },
    tag: {
      tokens: [['text', '/tag/list']], defaults: [], requirements: [], hosttokens: [],
    },
    tag_entries: {
      tokens: [['variable', '/', '[^/]++', 'page'], ['variable', '/', '[^/]++', 'slug'], ['text', '/tag/list']], defaults: { page: '1' }, requirements: [], hosttokens: [],
    },
    api_get_entries_tags: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['text', '/tags'], ['variable', '/', '[^/]++', 'entry'], ['text', '/api/entries']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    api_post_entries_tags: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['text', '/tags'], ['variable', '/', '[^/]++', 'entry'], ['text', '/api/entries']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    api_delete_entries_tags: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['variable', '/', '[^/\\.]++', 'tag'], ['text', '/tags'], ['variable', '/', '[^/]++', 'entry'], ['text', '/api/entries']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    api_get_tags: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['text', '/api/tags']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    api_delete_tag_label: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['text', '/api/tag/label']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    api_delete_tags_label: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['text', '/api/tags/label']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    api_delete_tag: {
      tokens: [['variable', '.', 'xml|json|txt|csv|pdf|epub|mobi|html', '_format'], ['variable', '/', '[^/\\.]++', 'tag'], ['text', '/api/tags']], defaults: { _format: 'json' }, requirements: { _format: 'xml|json|txt|csv|pdf|epub|mobi|html' }, hosttokens: [],
    },
    homepage: {
      tokens: [['variable', '/', '\\d+', 'page']], defaults: { page: 1 }, requirements: { page: '\\d+' }, hosttokens: [],
    },
    fos_user_security_logout: {
      tokens: [['text', '/logout']], defaults: [], requirements: [], hosttokens: [],
    },
    craue_config_settings_modify: {
      tokens: [['text', '/settings']], defaults: [], requirements: [], hosttokens: [],
    },
  },
  prefix: '',
  host: 'localhost',
  scheme: 'http',
});
