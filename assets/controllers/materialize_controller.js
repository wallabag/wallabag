import { Controller } from '@hotwired/stimulus';
import $ from 'jquery';

export default class extends Controller {
  connect() {  // sidenav
    $('.sidenav').sidenav();
    $('select').formSelect();
    $('.collapsible[data-collapsible="accordion"]').collapsible();
    $('.collapsible[data-collapsible="expandable"]').collapsible({
      accordion: false,
    });

    $('.dropdown-trigger').dropdown({ hover: false });
    $('.dropdown-trigger[data-covertrigger="false"][data-constrainwidth="false"]').dropdown({
      hover: false,
      coverTrigger: false,
      constrainWidth: false,
    });

    $('.tabs').tabs();
    $('.tooltipped').tooltip();
  }
}
