import './bootstrap';

/*
 * Alpine, from the bundle.
 *
 * It used to arrive inside Livewire's script tag (~515 KB, parsed on every
 * page load) even though nothing in the project is a Livewire component.
 * This is the same Alpine, at about a thirtieth of the size.
 *
 * This module is deferred, so every inline <script> in the page — the ones
 * that define x-data component functions — has already run by the time
 * Alpine starts. That is the same ordering Livewire gave them.
 */
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
