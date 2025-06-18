import 'bootstrap';
import './styles/app.css';
import 'bootstrap/dist/css/bootstrap.min.css';


import './js/barba-transition.js'
import { startStimulusApp } from '@symfony/stimulus-bundle';
const app = startStimulusApp();

console.log('This log comes from assets/app.js ! 🎉🎉🎉');
