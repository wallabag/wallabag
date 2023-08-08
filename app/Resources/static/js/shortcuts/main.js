import Mousetrap from 'mousetrap';

/* Shortcuts */

/* Go to */
Mousetrap.bind('g u', () => { window.location.href = Routing.generate('homepage'); });
Mousetrap.bind('g s', () => { window.location.href = Routing.generate('starred'); });
Mousetrap.bind('g r', () => { window.location.href = Routing.generate('archive'); });
Mousetrap.bind('g a', () => { window.location.href = Routing.generate('all'); });
Mousetrap.bind('g t', () => { window.location.href = Routing.generate('tag'); });
Mousetrap.bind('g c', () => { window.location.href = Routing.generate('config'); });
Mousetrap.bind('g i', () => { window.location.href = Routing.generate('import'); });
Mousetrap.bind('g d', () => { window.location.href = Routing.generate('developer'); });
Mousetrap.bind('?', () => { window.location.href = Routing.generate('howto'); });
Mousetrap.bind('g l', () => { window.location.href = Routing.generate('fos_user_security_logout'); });
