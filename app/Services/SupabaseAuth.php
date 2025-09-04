<?php
namespace App\Services;

use PHPSupabase\Service;

class SupabaseAuth
{
    protected $service;

    public function __construct()
    {
        $this->service = new Service(
            env('SUPABASE_KEY'),
            env('SUPABASE_URL')
        );
    }

    public function login($email, $password)
    {
        $auth = $this->service->createAuth();
        $response = $auth->signInWithEmailAndPassword($email, $password);
        if ($response['user']) {
            $user = $response['user'];
            $userData = $this->service->initializeDatabase('users', 'id')
                ->fetchById($user['id'])
                ->getResult();
            session(['user' => [
                'id' => $user['id'],
                'role' => $userData[0]['role'],
                'collector_company_id' => $userData[0]['collector_company_id'],
                'council_id' => $userData[0]['council_id'],
            ]]);
            return true;
        }
        return false;
    }

    public function logout()
    {
        $this->service->createAuth()->signOut();
        session()->flush();
    }

    public function isCollectionAdmin()
    {
        return session('user.role') === 'collection_admin';
    }
}