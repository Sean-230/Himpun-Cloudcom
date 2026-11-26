import Alpine from 'alpinejs';
import prokerDetail from './proker-detail';
import prokerForm from './proker-form';
import roleSwitch from './role-switch';

Alpine.data('prokerForm', prokerForm);
Alpine.data('prokerDetail', prokerDetail);
Alpine.data('roleSwitch', roleSwitch);
window.Alpine = Alpine;
Alpine.start();
