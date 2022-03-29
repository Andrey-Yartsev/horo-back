<?php

namespace App\AstrologyApi;

class Sign
{
    const COMPATIBILITY_MAP = [
        "Capricorn" => [
            "Capricorn" => "Beneficial relationship",
            "Aquarius" => "Enlightening and a delight",
            "Pisces" => "Achievements of goals"
        ],
        "Pisces" => [
            "Pisces" => "Spiritual connection"
        ],
        "Leo" => [
            "Capricorn" => "Extremely devoted",
            "Scorpio" => "Powerful personalities",
            "Leo" => "Great deal of attention",
            "Sagittarius" => "Full of life",
            "Virgo" => "Understanding and appreciating the other",
            "Libra" => "Smooth relationship",
            "Aquarius" => "Energetic and unstoppable",
            "Pisces" => "Dreamers at heart"
        ],
        "Gemini" => [
            "Libra" => "Intellectual team",
            "Leo" => "High spirited and playful",
            "Sagittarius" => "Sensation and mental freedom",
            "Capricorn" => "Totally opposite",
            "Aquarius" => "Inspiration and support",
            "Cancer" => "Curious relationship",
            "Virgo" => "Short term relationship",
            "Gemini" => "Vivacious pair",
            "Pisces" => "Opened minded and flexible",
            "Scorpio" => "Intense and passionate"
        ],

        "Aquarius" => [
            "Pisces" => "Karmic link",
            "Aquarius" => "Intellectual partners"
        ],

        "Virgo" => [
            "Capricorn" => "Highly pleasant relationship",
            "Sagittarius" => "Stable and happy",
            "Virgo" => "Wonderful pair",
            "Pisces" => "Empathy and commitment",
            "Libra" => "Great balance",
            "Scorpio" => "Fulfilling union",
            "Aquarius" => "Thrive on differences"
        ],

        "Taurus" => [
            "Virgo" => "Equally sincere",
            "Sagittarius" => "Different personalities",
            "Aquarius" => "Desire to be successful",
            "Pisces" => "Spiritual connection",
            "Taurus" => "Highly romantic",
            "Cancer" => "Long term commitment",
            "Libra" => "Long lasting relationship",
            "Capricorn" => "Sensible and practical",
            "Gemini" => "Learn from each other",
            "Leo" => "Dominating and stubborn",
            "Scorpio" => "Deep desires"
        ],

        "Aries" => [
            "Aquarius" => "Respect and admiration",
            "Cancer" => "Extremely protective",
            "Aries" => "Understanding and passion",
            "Taurus" => "Love and passion",
            "Capricorn" => "Initiators",
            "Pisces" => "Discussions and negotiation",
            "Gemini" => "Balance and harmony",
            "Scorpio" => "Not ready to compromise",
            "Virgo" => "Fifty/fifty",
            "Libra" => "The chemistry between",
            "Sagittarius" => "Endless resources of energy",
            "Leo" => "Excitement and action"
        ],

        "Scorpio" => [
            "Capricorn" => "Sincerity in all situations",
            "Aquarius" => "Powerful personalities",
            "Sagittarius" => "Always on the go",
            "Scorpio" => "Obsessed with one another",
            "Pisces" => "Splendid union"
        ],

        "Sagittarius" => [
            "Capricorn" => "Оpposites",
            "Pisces" => "Philosophical cravings",
            "Aquarius" => "Creative partnership",
            "Sagittarius" => "Near perfect relationship"
        ],

        "Libra" => [
            "Scorpio" => "Deep emotional connection",
            "Sagittarius" => "New horizons",
            "Capricorn" => "Learn to compromise a bit",
            "Aquarius" => "Energy and enthusiasm",
            "Pisces" => "Energy and enthusiasm",
            "Libra" => "Well balanced and beautiful"
        ],

        "Cancer" => [
            "Virgo" => "Sincere and devoted",
            "Sagittarius" => "Stable and happy relationship",
            "Cancer" => "Loyalty and dedication",
            "Aquarius" => "Ambitious and determined",
            "Scorpio" => "Strong sexual attraction",
            "Capricorn" => "Mutual commitments",
            "Pisces" => "Emotion and compassion",
            "Leo" => "Mutual commitment",
            "Libra" => "Secure and assured relationship"
        ],
    ];
}
