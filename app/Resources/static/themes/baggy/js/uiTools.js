import $ from 'jquery';

function toggleSaveLinkForm(url, event) {
  $('#add-link-result').empty();

  const $bagit = $('#bagit');
  const $bagitForm = $('#bagit-form');

  $bagit.toggleClass('active-current');

  // only if bag-it link is not presented on page
  if ($bagit.length === 0) {
    if (event !== 'undefined' && event) {
      $bagitForm.css({ position: 'absolute', top: event.pageY, left: event.pageX - 200 });
    } else {
      $bagitForm.css({ position: 'relative', top: 'auto', left: 'auto' });
    }
  }

  const searchForm = $('#search-form');
  const plainUrl = $('#plainurl');
  if (searchForm.length !== 0) {
    $('#search').removeClass('current');
    $('#search-arrow').removeClass('arrow-down');
    searchForm.hide();
  }
  $bagitForm.toggle();
  $('#content').toggleClass('opacity03');
  if (url !== 'undefined' && url) {
    plainUrl.val(url);
  }
  plainUrl.focus();
}

export default toggleSaveLinkForm;
