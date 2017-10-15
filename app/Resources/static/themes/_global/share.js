import './share.scss';

function requireAll(r) { r.keys().forEach(r); }
requireAll(require.context('./img/', true, /\.(jpg|png|gif|svg|ico)$/));
