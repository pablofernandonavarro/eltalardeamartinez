<?php

namespace App\Actions\Fortify;

use App\Models\User;
use App\Notifications\NewUserRegistered;
use App\Role;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        Validator::make($input, [
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique(User::class),
            ],
            'password' => $this->passwordRules(),
            'requested_unit_id' => ['required', 'exists:units,id'],
        ])->validate();

        $user = User::create([
            'name' => $input['name'],
            'email' => $input['email'],
            'password' => $input['password'],
            'requested_unit_id' => $input['requested_unit_id'],
            'role' => null,
            'approved_at' => null,
        ]);

        $user->load('requestedUnit');

        User::where('role', Role::Admin->value)->each(
            fn ($admin) => $admin->notify(new NewUserRegistered($user))
        );

        return $user;
    }
}
