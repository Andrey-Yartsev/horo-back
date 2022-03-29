<?php

namespace Database\Seeders;

use App\Models\Planet;
use Illuminate\Database\Seeder;

class PlanetSeeder extends Seeder
{
    protected $planets = [
        [
            'name' => 'Moon',
            'tags' => ["Emotions", "Feelings", "Instincts", "Unconsciousness"],
            'description' => "Emotion and feeling. If your sun sign represents your outward personality, your moon sign focuses inward, revealing your emotional core. The Moon governs over the shadowy, more vulnerable sides of ourselves — the parts we may not show to others unless we feel intimate and safe with them — as well as the things we need to feel protected, comfortable, and emotionally secure.\n\nThe moon moves into a new sign every two to three days, and represents the emotional nature of the sign it's in.",
        ],
        [
            'name' => 'Sun',
            'tags' => ["The self", "Creative energy", "Lifeforce"],
            'description' => "The Sun is the center of our universe, and in astrology, it represents the center of ourselves. You are who you are because of your sun sign, and truly understanding and accepting this integral part of yourself helps you lead a positive, well-rounded life.\n\nThe Sun shifts into a new sign every month, and as a personal planet, it represents the ego, the inner self, willpower, and lifeforce. When someone says “what’s your sign?” they’re actually asking “what’s your sun sign?”",
        ],
        [
            'name' => 'Mercury',
            'tags' => ["Mind", "Communication", "Intellect"],
            'description' => "You probably know this planet more for its infamous retrograde periods than anything else, but actually, when it's not turning everything in your life upside, this quick-witted master of communication, timing, and intellect is really cool. Your Mercury sign informs how you listen, read, write, and talk, so understanding how your Mercury sign works can help you understand how best you’re able to learn and teach. This also explains why Mercury in retrograde means miscommunications (and missed flights!).\n\nMercury moves into a new sign every three to four weeks, and is the planet that rules our rational mind, our capacity to collect and sort out information and to pass it on to others. It has power over our written and spoken word and our curiosity.",
        ],
        [
            'name' => 'Venus',
            'tags' => ["Love", "Attraction", "Art", "Beauty", "Harmony"],
            'description' => "Love and beauty. Our Venus sign determines how we give and receive love, and it’s where we find comfort and support within ourselves and others. Venus rules all the delightful, swoon-worthy, aesthetically pleasing things in life. Named after the goddess of love herself, Venus loves to love, and to be immersed in the aestheticism and beauty of the world. Venus is also associated with money — specifically the money we spend more frivolously on things that bring us pleasure and joy. It's about the way you love, how you determine value, and the ways you experience (and indulge in) sensual pleasure and luxury.\n\nVenus moves signs every four to five weeks, and is associated with the signs Taurus and Libra. It's known to be the planet of love, beauty, and money.",
        ],
        [
            'name' => 'Mars',
            'tags' => ["Action", "Energy", "Competition", "Passion"],
            'description' => "Will, energy and courage. Fiery and passionate, Mars governs how we assert ourselves — our “fight” in the battle (Mars is the god of war, after all). It’s here that we can fall into aggression and volatility, but if harnessed correctly, our Mars sign can be our greatest source of bravery and self-empowerment. Mars also shows us how we chase our goals and relates to our libidos and sexual energies, too. And you may have heard \"men are from Mars, women are from Venus,\" right? Well, we actually all have a nice balance of both, Venus is the butterflies in our stomach and emotion, while Mars is the primal, physical instinct.\n\nMars is the heated passion behind our actions, and shifts into a new sign every six to seven weeks, changing the way we take initiative. Mars is the ruler of Aries, and is the slowest moving of the personal planets.",
        ],
        [
            'name' => 'Jupiter',
            'tags' => ["Luck", "Growth", "Expansion", "Optimism"],
            'description' => "Jupiter is the largest planet in the solar system, and it also carries with it the biggest load of luck, positivity, and optimism of the bunch, bringing growth, opportunity, and good vibes along with its massive presence. Jupiter is the planet that governs the sign of happy-go-lucky Sagittarius, and the planet represents many of the themes that this sign concerns itself with. This giant planet fuels all sorts of positive optimism in our lives, and is considered to be the planet of miracles, hope, and opportunity.\n\nJupiter is the planet that pushes us to continue exploring, asking questions, and look for answers. It's also the planet that can lead us into overindulgence. Jupiter moves into a new sign every two to three years.",
        ],
        [
            'name' => 'Saturn',
            'tags' => ["Structure", "Discipline", "Responsibility"],
            'description' => "Discipline and commitment. If the Moon is our cosmic mom, then Saturn is totally our cosmic father figure. Think of this planet as the stern parent who's a little overly rigid, old-fashioned, and strict, but ultimately it's there to help you grow and learn to be more responsible. Saturn is the solar system’s task master, known for sending major challenges your way — but for your own good, of course. Your Saturn Return is to blame for your quarter-life crisis, and whenever you’re feeling particularly put out by a hard situation, it’s usually Saturn’s doing, forcing you to work through the tough stuff to become a better person.\n\nSaturn is the laws, boundaries, and limits set by society. It shapes our sense of personal duty during times of hardship. It moves into a new sign every two to three years, taking its sweet time with each sign to teach its hard-fought lessons.",
        ],
        [
            'name' => 'Uranus',
            'tags' => ["Eccentricity", "Changes", "Originality", "Reformation"],
            'description' => "Individuality and change. Expect the unexpected with Uranus, as this planet is all about shaking up the norms, balking at tradition, and challenging the status quo. It's progressive, forward-thinking, and hyper-creative, but also prone to abrupt shifts and changes.  In fact, this planet is sometimes likened to a lightning bolt given the way it shocks us with sudden insights and inspirations. Uranus is the most unpredictable of the planets, but it's innovative and one-of-a-kind energy is part of what makes life interesting.\n\nUranus is an outer planet and takes seven years to orbit the sun, which, as we mentioned earlier, means that there are large groups of people who share a Uranus sign. It takes a full seven years to move from one sign into another, defining the ideas of entire generations of people each time it moves into a new sign.",
        ],
        [
            'name' => 'Neptune',
            'tags' => ["Dreams", "Intuition", "Mysticism", "Imagination"],
            'description' => "Intuition and healing. Dreamy, other-worldly Neptune is the most mystical and ethereal of the bunch. Neptune rules our dreams and ideals; it represents things that aren't quite as they appear, the illusive, and the unreal. It is the planet of receptivity, imagination, cloudiness, confusion, delusion, illusion, and unreality. This planet's meanings are as deep as its color is blue, as it's representative of psychic intuition and spiritual attunement, as well as dreams and artistic expression. Neptune is known for being a little out there (and we don’t just mean its distance from the sun). But if you can avoid falling into the escapist tendencies this planet can induce, it can help you understand your sensitivity, psychic powers, and spiritual depth.\n\nNeptune moves into a new sign every 10 to 12 years influences our urge for spiritual healing, but at its worst can lead us toward escapism through the use of mind-altering drugs.",
        ],
        [
            'name' => 'Pluto',
            'tags' => ["Power", "Transformation", "Rebirth", "Evolution"],
            'description' => "Transformation and power. Even if Pluto’s planetary distinction is up for debate, its incredible astrological influence is not. In astrology, Pluto is as planet as can be — and is a very intense and sometimes dark one, at that. Pluto reigns over our destinies, giving life to our biggest desires while simultaneously reminding us of how short life is, so we better do something memorable while we’re here. Because Pluto is the furthest planet from the Earth, its effects are often felt on a more collective, societal level than a personal level. Pluto will transform the areas of your life that need to undergo regeneration, whether you asked for it or not.\n\nBeing such an unpredictable planet, Pluto moves into a new sign every 12 to 15 years.",
        ],
    ];

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        foreach ($this->planets as $seederPlanet) {
            if (!$planet = Planet::where('name', $seederPlanet['name'])->first()) {
                $planet = new Planet;
            }

            $planet->fill($seederPlanet);
            $planet->save();
        }

        echo "Planets were seeded.\n";
    }
}
