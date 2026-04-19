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
            'label'           => 'Healthcare',
            'description'     => 'Expert Medical Consultations',
            'icon'            => '🏥',
            'emoji'           => '⚕️',
            'employee_label'  => 'Specialist',
            'booking_label'   => 'Appointment',
            'slot_label'      => 'Time Slot',
            'customer_label'  => 'Patient',

            'primary'         => '#f97316', // Standardized to Orange
            'primary_dark'    => '#ea580c',
            'accent'          => '#fff7ed',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #1e3a8a 0%, #1e1b4b 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(180deg, #1e3a8a 0%, #172554 100%)',
            'card_bg'         => '#ffffff',
            'card_border'     => '#e2e8f0',
            'card_shadow'     => '0 10px 30px -10px rgba(0, 0, 0, 0.2)',
            
            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-orange-100 text-orange-700 border-orange-200',
            'btn_classes'     => 'bg-orange-500 hover:bg-orange-600 text-white shadow-orange-500/20',
            'filter_active'   => 'bg-orange-500 text-white shadow-orange-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['General Consultation', 'Cardiac Screening', 'Diagnostics'],

            'is_dark'         => true,
            'nav_bg'          => '#ffffff', 
            'nav_text'        => '#0f172a',
            'nav_blur_bg'     => 'rgba(255, 255, 255, 0.95)',
        ],

        'barber' => [
            'key'             => 'barber',
            'label'           => 'Beauty & Spa',
            'description'     => 'Premium Grooming & Aesthetics',
            'icon'            => '💇',
            'emoji'           => '✨',
            'employee_label'  => 'Stylist',
            'booking_label'   => 'Session',
            'slot_label'      => 'Service Slot',
            'customer_label'  => 'Client',

            'primary'         => '#f97316',
            'primary_dark'    => '#ea580c',
            'accent'          => '#fff7ed',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #1e3a8a 0%, #1e1b4b 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(180deg, #1e3a8a 0%, #172554 100%)',
            'card_bg'         => '#ffffff',
            'card_border'     => '#e2e8f0',
            'card_shadow'     => '0 10px 30px -10px rgba(0, 0, 0, 0.2)',

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
            'nav_bg'          => '#ffffff', 
            'nav_text'        => '#0f172a',
            'nav_blur_bg'     => 'rgba(255, 255, 255, 0.95)',
        ],

        'activity' => [
            'key'             => 'activity',
            'label'           => 'Sports & Gym',
            'description'     => 'Athletic Training & Performance',
            'icon'            => '⚡',
            'emoji'           => '🏆',
            'employee_label'  => 'Coach',
            'booking_label'   => 'Workout',
            'slot_label'      => 'Training Slot',
            'customer_label'  => 'Client',

            'primary'         => '#f97316',
            'primary_dark'    => '#ea580c',
            'accent'          => '#fff7ed',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #1e3a8a 0%, #1e1b4b 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(180deg, #1e3a8a 0%, #172554 100%)',
            'card_bg'         => '#ffffff',
            'card_border'     => '#e2e8f0',
            'card_shadow'     => '0 10px 30px -10px rgba(0, 0, 0, 0.2)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-orange-100 text-orange-700 border-orange-200',
            'btn_classes'     => 'bg-orange-500 hover:bg-orange-600 text-white shadow-orange-500/20',
            'filter_active'   => 'bg-orange-500 text-white shadow-orange-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Personal Coaching', 'Yoga Session', 'Strength Training'],

            'is_dark'         => true,
            'nav_bg'          => '#ffffff', 
            'nav_text'        => '#0f172a',
            'nav_blur_bg'     => 'rgba(255, 255, 255, 0.95)',
        ],

        'training' => [
            'key'             => 'training',
            'label'           => 'Academies',
            'description'     => 'Educational & Skill Development',
            'icon'            => '🎓',
            'emoji'           => '📘',
            'employee_label'  => 'Instructor',
            'booking_label'   => 'Class',
            'slot_label'      => 'Academic Slot',
            'customer_label'  => 'Student',

            'primary'         => '#f97316',
            'primary_dark'    => '#ea580c',
            'accent'          => '#fff7ed',
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #1e3a8a 0%, #1e1b4b 100%)',
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(180deg, #1e3a8a 0%, #172554 100%)',
            'card_bg'         => '#ffffff',
            'card_border'     => '#e2e8f0',
            'card_shadow'     => '0 10px 30px -10px rgba(0, 0, 0, 0.2)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-orange-100 text-orange-700 border-orange-200',
            'btn_classes'     => 'bg-orange-500 hover:bg-orange-600 text-white shadow-orange-500/20',
            'filter_active'   => 'bg-orange-500 text-white shadow-orange-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Public Speaking', 'Coding Bootcamp', 'Design Thinking'],

            'is_dark'         => true,
            'nav_bg'          => '#ffffff', 
            'nav_text'        => '#0f172a',
            'nav_blur_bg'     => 'rgba(255, 255, 255, 0.95)',
        ],

        'consultant' => [
            'key'             => 'consultant',
            'label'           => 'Professional',
            'description'     => 'Business & Strategy Advisory',
            'icon'            => '💼',
            'emoji'           => '🖊️',
            'employee_label'  => 'Advisor',
            'booking_label'   => 'Strategy Session',
            'slot_label'      => 'Available Slot',
            'customer_label'  => 'Client',

            'primary'         => '#f97316', // Orange 500 (Image 1 Primary)
            'primary_dark'    => '#ea580c', // Orange 600
            'accent'          => '#fff7ed', // Orange 50
            'text_on_primary' => '#ffffff',

            'body_bg'         => 'linear-gradient(180deg, #1e3a8a 0%, #1e1b4b 100%)', // Deep Blue (Image 2)
            'body_text'       => '#f8fafc',
            'hero_gradient'   => 'linear-gradient(180deg, #1e3a8a 0%, #172554 100%)',
            'card_bg'         => '#ffffff',
            'card_border'     => '#e2e8f0',
            'card_shadow'     => '0 10px 30px -10px rgba(0, 0, 0, 0.2)',

            'font_heading'    => 'Outfit',
            'border_radius'   => '1.5rem',
            'animation'       => 'fade-in',
            'overlay_opacity' => '0.05',

            'badge_classes'   => 'bg-orange-100 text-orange-700 border-orange-200',
            'btn_classes'     => 'bg-orange-500 hover:bg-orange-600 text-white shadow-orange-500/20',
            'filter_active'   => 'bg-orange-500 text-white shadow-orange-500/10',
            'filter_idle'     => 'bg-white/10 backdrop-blur-xl text-white border border-white/20 hover:bg-white/20',
            'services'        => ['Financial Planning', 'Legal Consultation', 'Operational Audit'],

            'is_dark'         => true,
            'nav_bg'          => '#ffffff', 
            'nav_text'        => '#0f172a',
            'nav_blur_bg'     => 'rgba(255, 255, 255, 0.95)',
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
