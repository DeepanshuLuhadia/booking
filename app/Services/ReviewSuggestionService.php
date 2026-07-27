<?php

namespace App\Services;

class ReviewSuggestionService
{
    /**
     * Return 3 contextual review message suggestions based on star rating
     * and vendor category. Zero external API cost — pure template logic.
     */
    public function getSuggestions(int $rating, ?string $category = null): array
    {
        $cat = strtolower($category ?? 'general');

        return match ($rating) {
            5 => $this->fiveStarSuggestions($cat),
            4 => $this->fourStarSuggestions($cat),
            3 => $this->threeStarSuggestions($cat),
            2 => $this->twoStarSuggestions($cat),
            1 => $this->oneStarSuggestions($cat),
            default => [],
        };
    }

    /**
     * Get all suggestions for a specific category to pass to the frontend at once.
     */
    public function getAllForCategory(?string $category = null): array
    {
        $cat = strtolower($category ?? 'general');
        return [
            5 => $this->fiveStarSuggestions($cat),
            4 => $this->fourStarSuggestions($cat),
            3 => $this->threeStarSuggestions($cat),
            2 => $this->twoStarSuggestions($cat),
            1 => $this->oneStarSuggestions($cat),
        ];
    }

    protected function fiveStarSuggestions(string $cat): array
    {
        $base = [
            'Absolutely outstanding experience! Will definitely be coming back.',
            'Best service I\'ve had in a long time. Highly recommend to everyone.',
            'Professional, punctual, and exceeded all expectations. 10/10!',
            'Fantastic service from start to finish. Everything was perfect.',
            'A truly 5-star experience. The attention to detail was incredible.',
            'Highly professional and welcoming. Couldn\'t have asked for better.',
            'Exceptional quality and great value. I\'ll be a regular for sure.',
            'They went above and beyond! Really impressed with the standard of service.'
        ];

        return match (true) {
            in_array($cat, ['barber', 'salon', 'beauty']) => [
                'Amazing transformation! The stylist really understood what I wanted.',
                'Perfect haircut and such a relaxing atmosphere. My new go-to place!',
                'Top-notch grooming experience. Clean, professional, and friendly staff.',
                'Left feeling refreshed and looking great. Fantastic attention to detail.',
                'Best styling experience I\'ve had. They truly know what they\'re doing.',
                'Beautiful results and excellent customer care. Highly recommend!',
                'Flawless service. The ambiance is perfect and the staff are incredibly talented.'
            ],
            in_array($cat, ['clinic', 'doctor', 'health', 'dental']) => [
                'Excellent doctor. Very thorough examination and clear explanations.',
                'Minimal wait time and very caring staff. Best clinic experience ever.',
                'Felt truly heard and well taken care of. Highly recommend this clinic.',
                'Very professional medical care. The staff made me feel completely at ease.',
                'Spotless clinic and an incredibly knowledgeable doctor. Great experience.',
                'Fast, efficient, and compassionate care. 10/10 recommendation.',
                'The diagnosis and treatment plan were explained perfectly. Very reassuring.'
            ],
            in_array($cat, ['sports', 'gym', 'fitness', 'turf']) => [
                'Great facilities and well-maintained equipment. Worth every rupee!',
                'Amazing sports experience! The booking was seamless and the venue is top-class.',
                'Clean, professional, and the staff are very helpful. Will be back!',
                'Top tier turf with excellent lighting and amenities. Loved playing here.',
                'Best fitness environment in the city. The energy here is fantastic.',
                'Extremely well managed sports facility. Booking was quick and easy.',
                'Perfect venue for our game. The pitch quality was exceptional.'
            ],
            in_array($cat, ['training', 'consultant', 'coaching']) => [
                'Incredibly knowledgeable trainer. Learned so much in just one session!',
                'Expert guidance and personalised attention. Money well spent.',
                'The consultation was thorough, practical, and truly eye-opening.',
                'Brilliant coaching! They broke down complex concepts perfectly.',
                'Highly professional consultant who provided immense value today.',
                'Best training session I\'ve attended. Highly interactive and useful.',
                'Actionable advice and deep expertise. Highly recommended.'
            ],
            default => $base,
        };
    }

    protected function fourStarSuggestions(string $cat): array
    {
        return match (true) {
            in_array($cat, ['barber', 'salon', 'beauty']) => [
                'Really good service overall. Just a tiny bit of wait, but worth it.',
                'Great styling work! The ambiance could be slightly better, but very happy.',
                'Skilled professionals and fair pricing. Would recommend with confidence.',
                'Very pleased with the result. A few minor delays but great quality.',
                'Nice salon and good staff. Will likely return for my next cut.',
                'Solid experience. The service was good but lacked a bit of premium feel.'
            ],
            in_array($cat, ['clinic', 'doctor', 'health', 'dental']) => [
                'Good consultation. The doctor was knowledgeable and attentive.',
                'Well-organized clinic. Slight delay but the care was excellent.',
                'Professional service and clean environment. Very satisfied overall.',
                'Doctor was great, though the waiting area could be a bit more comfortable.',
                'Efficient medical service. Staff were helpful and the process was smooth.',
                'Felt well cared for. Just minor administrative hiccups during check-in.'
            ],
            in_array($cat, ['sports', 'gym', 'fitness', 'turf']) => [
                'Good facilities and friendly staff. Minor improvements needed but solid.',
                'Enjoyed the session! Booking process was smooth and venue was clean.',
                'Great value for money. Would be perfect with slightly better maintenance.',
                'Solid sports venue. Lighting was good, just wish parking was easier.',
                'Fun experience and good equipment. Could use a bit more ventilation.',
                'Good turf quality and decent amenities. Overall a very good time.'
            ],
            default => [
                'Very good experience overall. Just small things that could improve.',
                'Impressed with the quality of service. Would happily visit again.',
                'Professional and reliable. A solid choice for anyone looking.',
                'Great service, friendly staff. Almost perfect!',
                'Really enjoyed the service today. Good value for money.',
                'Overall a highly positive experience. Would recommend to friends.'
            ],
        };
    }

    protected function threeStarSuggestions(string $cat): array
    {
        return match (true) {
            in_array($cat, ['barber', 'salon', 'beauty']) => [
                'Decent service but the wait was longer than expected.',
                'Average experience. The result was okay but not what I had in mind.',
                'Fair pricing but could improve on attention to detail.',
                'Okay cut, but the staff seemed a bit rushed today.',
                'Met my basic needs but lacked the finishing touches I expected.',
                'Not bad, but I have had better experiences elsewhere.'
            ],
            in_array($cat, ['clinic', 'doctor', 'health', 'dental']) => [
                'The doctor was okay but felt a bit rushed during consultation.',
                'Average experience. Wait time was long but treatment was decent.',
                'Acceptable service. Could improve communication and follow-up.',
                'The medical care was fine, but the wait times need serious improvement.',
                'Okay consultation, though I wish things were explained more clearly.',
                'Standard clinic visit. Nothing terrible, but nothing great either.'
            ],
            default => [
                'It was an okay experience. Nothing exceptional, but not bad either.',
                'Average service. There\'s room for improvement in a few areas.',
                'Met basic expectations. Would consider giving another chance.',
                'Decent value for money, but the overall experience was just average.',
                'Service was acceptable but quite slow today.',
                'An okay visit. Hopefully, it\'s better next time.'
            ],
        };
    }

    protected function twoStarSuggestions(string $cat): array
    {
        return match (true) {
            in_array($cat, ['barber', 'salon', 'beauty']) => [
                'The result wasn\'t what I asked for. Expected better communication.',
                'Long wait and underwhelming service. Needs improvement.',
                'Not satisfied with the quality. Would have appreciated more care.',
                'The styling felt very rushed and unpolished. Disappointed.',
                'Unprofessional atmosphere and the end result was below average.',
                'They didn\'t listen to my instructions. Wouldn\'t recommend.'
            ],
            in_array($cat, ['clinic', 'doctor', 'health', 'dental']) => [
                'Very long wait time and felt rushed during the consultation.',
                'Expected better hygiene standards and more attentive staff.',
                'The experience was below expectations. Needs significant improvement.',
                'Poor administrative process and the doctor seemed distracted.',
                'Felt like just another number. Lack of personalized care.',
                'Unhelpful staff and confusing instructions regarding my treatment.'
            ],
            default => [
                'Below expectations. The service quality needs significant improvement.',
                'Not a great experience. Would have expected better for the price.',
                'Disappointed with the overall experience. Hope they improve.',
                'Very slow service and poor communication from the staff.',
                'The quality simply wasn\'t there today. Quite disappointing.',
                'Needs a lot of work to meet professional standards.'
            ],
        };
    }

    protected function oneStarSuggestions(string $cat): array
    {
        return match (true) {
            in_array($cat, ['barber', 'salon', 'beauty']) => [
                'Very poor experience. The service did not match what was promised.',
                'Unhygienic conditions and unprofessional behaviour. Would not return.',
                'Extremely disappointed. The result was nothing like what I requested.',
                'Terrible service. Ruined my hair and refused to fix it.',
                'Rude staff and completely unacceptable hygiene standards.',
                'A complete waste of money and time. Do not recommend at all.'
            ],
            in_array($cat, ['clinic', 'doctor', 'health', 'dental']) => [
                'Unacceptable wait time and the staff were dismissive and unhelpful.',
                'Poor hygiene and unprofessional conduct. Would not recommend.',
                'Very disappointing experience. Expected much better care standards.',
                'Terrible patient care. Felt ignored and the diagnosis was rushed.',
                'Horrible administrative staff and extremely unhygienic premises.',
                'Would give zero stars if I could. Avoid this clinic.'
            ],
            default => [
                'Very poor experience. Would not recommend to others.',
                'Extremely disappointed with the service quality and professionalism.',
                'Did not meet even basic expectations. Needs major improvement.',
                'Terrible customer service and completely unacceptable delays.',
                'A frustrating and thoroughly disappointing experience.',
                'Completely unprofessional. I regret spending my money here.'
            ],
        };
    }
}
