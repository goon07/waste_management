<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller; // Add this import

class HomeController extends Controller
{
 public function index()
    {
        $services = [
            [
                'icon' => 'fa-trash-alt',
                'title' => 'Residential Waste Collection',
                'description' => 'Regular scheduled pickups for households with proper segregation of recyclables and organic waste.'
            ],
            [
                'icon' => 'fa-building',
                'title' => 'Commercial Waste Solutions',
                'description' => 'Customized waste management plans for businesses, offices, and commercial establishments.'
            ],
            [
                'icon' => 'fa-recycle',
                'title' => 'Recycling Programs',
                'description' => 'Advanced recycling facilities that process paper, plastic, glass, and metal into reusable materials.'
            ],
            [
                'icon' => 'fa-leaf',
                'title' => 'Organic Waste Composting',
                'description' => 'Transforming food scraps and garden waste into nutrient-rich compost for urban farming.'
            ],
            [
                'icon' => 'fa-biohazard',
                'title' => 'Hazardous Waste Disposal',
                'description' => 'Safe collection and disposal of electronic waste, chemicals, batteries, and medical waste.'
            ],
            [
                'icon' => 'fa-chart-line',
                'title' => 'Waste Analytics',
                'description' => 'Data-driven insights to help communities and businesses reduce waste generation.'
            ]
        ];

        return view('welcome', compact('services'));
    }
	public function contactSubmit(Request $request)
{
    $validated = $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|email|max:255',
        'subject' => 'required|string|max:255',
        'message' => 'required|string',
    ]);

    // Handle form submission (e.g., save to database or send email)
    // For now, return a success message
    return redirect()->back()->with('success', 'Thank you for your message! We will get back to you soon.');
}

public function pickup()
{
    return view('pickup'); // Create a pickup.blade.php view
}

public function learnMore()
{
    return view('learn-more'); // Create a learn-more.blade.php view
}
public function newsletterSubscribe(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|max:255',
    ]);

    return redirect()->back()->with('success', 'Thank you for subscribing to our newsletter!');
}
 public function signin()
    {
        return view('auth.signin');
    }
}