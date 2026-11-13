import Alpine from 'alpinejs';
import prokerDetail from './proker-detail';
import prokerForm from './proker-form';

Alpine.data('prokerForm', prokerForm);
Alpine.data('prokerDetail', prokerDetail);
window.Alpine = Alpine;
Alpine.start();
