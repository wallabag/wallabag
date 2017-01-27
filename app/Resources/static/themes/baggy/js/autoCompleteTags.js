function split(val) {
  return val.split(/,\s*/);
}
function extractLast(term) {
  return split(term).pop();
}

export default { split, extractLast };
