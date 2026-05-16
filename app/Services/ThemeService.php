<?php

namespace App\Services;

class ThemeService
{
    /**
     * All role-based theme configurations.
     * Each theme defines colors, backgrounds, card styles, and UI identity.
     */
    protected static array $themes = [

        'doctor' => [
            'key'             => 'doctor',
            'label'           => 'Health',
            'description'     => 'Expert Medical Consultations',
            'icon'            => '🏥',
            'emoji'           => '⚕️',
            'employee_label'  => 'Specialist',
            'booking_label'   => 'Appointment',
            'slot_label'      => 'Time Slot',
            'customer_label'  => 'Patient',

            'primary'         => '#00c853',
            'primary_dark'    => '#64dd17',
            'accent'          => '#f0fff4',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #0f172a 0%, #020617 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(135deg, #00c853 0%, #64dd17 100%)',
            'card_bg'         => 'rgba(255, 255, 255, 0.05)',
            'card_border'     => 'rgba(0, 200, 83, 0.2)',
            'card_shadow'     => '0 0 30px rgba(0, 200, 83, 0.1)',
            
            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
            'btn_classes'     => 'bg-emerald-500 hover:bg-emerald-600 text-white shadow-emerald-500/20',
            'filter_active'   => 'bg-emerald-500 text-white shadow-emerald-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['General Consultation', 'Cardiac Screening', 'Diagnostics'],

            'is_dark'         => true,
            'nav_bg'          => 'transparent', 
            'nav_text'        => '#ffffff',
            'nav_blur_bg'     => 'rgba(15, 23, 42, 0.8)',
        ],

        'barber' => [
            'key'             => 'barber',
            'label'           => 'Beauty',
            'description'     => 'Premium Grooming & Aesthetics',
            'icon'            => '💇',
            'emoji'           => '✨',
            'employee_label'  => 'Stylist',
            'booking_label'   => 'Session',
            'slot_label'      => 'Service Slot',
            'customer_label'  => 'Client',

            'primary'         => '#ff6d00',
            'primary_dark'    => '#ffab40',
            'accent'          => '#fffaf0',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #0f172a 0%, #020617 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(135deg, #ff6d00 0%, #ffab40 100%)',
            'card_bg'         => 'rgba(255, 255, 255, 0.05)',
            'card_border'     => 'rgba(255, 109, 0, 0.2)',
            'card_shadow'     => '0 0 30px rgba(255, 109, 0, 0.1)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-orange-100 text-orange-700 border-orange-200',
            'btn_classes'     => 'bg-orange-500 hover:bg-orange-600 text-white shadow-orange-500/20',
            'filter_active'   => 'bg-orange-500 text-white shadow-orange-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Hair Styling', 'Skincare Treatment', 'Nail Artistry'],

            'is_dark'         => true,
            'nav_bg'          => 'transparent', 
            'nav_text'        => '#ffffff',
            'nav_blur_bg'     => 'rgba(15, 23, 42, 0.8)',
        ],

        'activity' => [
            'key'             => 'activity',
            'label'           => 'Sports',
            'description'     => 'Athletic Training & Performance',
            'icon'            => '⚡',
            'emoji'           => '🏆',
            'employee_label'  => 'Coach',
            'booking_label'   => 'Workout',
            'slot_label'      => 'Training Slot',
            'customer_label'  => 'Client',

            'primary'         => '#ffd600',
            'primary_dark'    => '#ffea00',
            'accent'          => '#fffff0',
            'text_on_primary' => '#000000',

            'body_bg'         => 'linear-gradient(180deg, #0f172a 0%, #020617 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(135deg, #ffd600 0%, #ffea00 100%)',
            'card_bg'         => 'rgba(255, 255, 255, 0.05)',
            'card_border'     => 'rgba(255, 214, 0, 0.2)',
            'card_shadow'     => '0 0 30px rgba(255, 214, 0, 0.1)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-yellow-100 text-yellow-700 border-yellow-200',
            'btn_classes'     => 'bg-yellow-500 hover:bg-yellow-600 text-white shadow-yellow-500/20',
            'filter_active'   => 'bg-yellow-500 text-white shadow-yellow-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Personal Coaching', 'Yoga Session', 'Strength Training'],

            'is_dark'         => true,
            'nav_bg'          => 'transparent', 
            'nav_text'        => '#ffffff',
            'nav_blur_bg'     => 'rgba(15, 23, 42, 0.8)',
        ],

        'training' => [
            'key'             => 'training',
            'label'           => 'Education',
            'description'     => 'Educational & Skill Development',
            'icon'            => '🎓',
            'emoji'           => '📘',
            'employee_label'  => 'Instructor',
            'booking_label'   => 'Class',
            'slot_label'      => 'Academic Slot',
            'customer_label'  => 'Student',

            'primary'         => '#7c3aed',
            'primary_dark'    => '#a78bfa',
            'accent'          => '#e8eaf6',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #0f172a 0%, #020617 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(135deg, #7c3aed 0%, #a78bfa 100%)',
            'card_bg'         => 'rgba(255, 255, 255, 0.05)',
            'card_border'     => 'rgba(124, 58, 237, 0.2)',
            'card_shadow'     => '0 0 30px rgba(124, 58, 237, 0.1)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-indigo-100 text-indigo-700 border-indigo-200',
            'btn_classes'     => 'bg-indigo-500 hover:bg-indigo-600 text-white shadow-indigo-500/20',
            'filter_active'   => 'bg-indigo-500 text-white shadow-indigo-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Public Speaking', 'Coding Bootcamp', 'Design Thinking'],

            'is_dark'         => true,
            'nav_bg'          => 'transparent', 
            'nav_text'        => '#ffffff',
            'nav_blur_bg'     => 'rgba(15, 23, 42, 0.8)',
        ],

        'consultant' => [
            'key'             => 'consultant',
            'label'           => 'Consultant',
            'description'     => 'Business & Strategy Advisory',
            'icon'            => '💼',
            'emoji'           => '🖊️',
            'employee_label'  => 'Advisor',
            'booking_label'   => 'Strategy Session',
            'slot_label'      => 'Available Slot',
            'customer_label'  => 'Client',

            'primary'         => '#2979ff',
            'primary_dark'    => '#00b0ff',
            'accent'          => '#e3f2fd',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #0f172a 0%, #020617 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(135deg, #2979ff 0%, #00b0ff 100%)',
            'card_bg'         => 'rgba(255, 255, 255, 0.05)',
            'card_border'     => 'rgba(41, 121, 255, 0.2)',
            'card_shadow'     => '0 0 30px rgba(41, 121, 255, 0.1)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-blue-100 text-blue-700 border-blue-200',
            'btn_classes'     => 'bg-blue-500 hover:bg-blue-600 text-white shadow-blue-500/20',
            'filter_active'   => 'bg-blue-500 text-white shadow-blue-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Financial Planning', 'Legal Consultation', 'Operational Audit'],

            'is_dark'         => true,
            'nav_bg'          => 'transparent', 
            'nav_text'        => '#ffffff',
            'nav_blur_bg'     => 'rgba(15, 23, 42, 0.8)',
        ],
    ];

    /**
     * Default base configuration for all themes.
     * Ensures mandatory fields are always present.
     */
    protected static array $baseConfig = [
        'booking_label'  => 'Appointment',
        'slot_label'     => 'Time Slot',
        'customer_label' => 'Customer',
        'employee_label' => 'Specialist',
        'primary'        => '#f97316',
        'primary_dark'   => '#ea580c',
        'accent'         => '#fff7ed',
        'badge_classes'  => 'bg-orange-100 text-orange-700 border-orange-200',
        'btn_classes'    => 'bg-orange-500 hover:bg-orange-600 text-white shadow-orange-500/20',
        'is_dark'        => true,
    ];

    /**
     * Get theme config for a given vendor role.
     */
    public static function getTheme(string $role = 'consultant'): array
    {
        $theme = static::$themes[$role] ?? static::$themes['consultant'];
        return array_merge(static::$baseConfig, $theme);
    }

    /**
     * Get all themes (for listing page filters).
     */
    public static function getAllThemes(): array
    {
        return static::$themes;
    }

    /**
     * Generate CSS custom properties string for injection into <style>.
     */
    public static function getCssVars(array $theme): string
    {
        $navBg = $theme['nav_bg'] ?? '#ffffff';
        $navText = $theme['nav_text'] ?? '#0f172a';

        return ":root {
            --cat1: {$theme['primary']};
            --cat2: {$theme['primary_dark']};
            --theme-primary: {$theme['primary']};
            --theme-primary-dark: {$theme['primary_dark']};
            --theme-accent: {$theme['accent']};
            --theme-text-on-primary: {$theme['text_on_primary']};
            --theme-hero-gradient: {$theme['hero_gradient']};
            --theme-card-bg: {$theme['card_bg']};
            --theme-card-border: {$theme['card_border']};
            --theme-card-shadow: {$theme['card_shadow']};
            --theme-body-bg: {$theme['body_bg']};
            --theme-body-text: {$theme['body_text']};
            --theme-nav-bg: {$navBg};
            --theme-nav-text: {$navText};
            --theme-radius: {$theme['border_radius']};
            --theme-font-heading: '{$theme['font_heading']}', sans-serif;
            --theme-overlay-opacity: {$theme['overlay_opacity']};
        }";
    }
}
