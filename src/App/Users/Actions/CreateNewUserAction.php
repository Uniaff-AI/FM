<?php
namespace App\Users\Actions;

use App\Users\Models\User;
use App\Users\DTO\CreateUserData;
use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Registered;
use Domain\Teams\Models\TeamFolderMember;
use Domain\Teams\Models\TeamFolderInvitation;
// Удалите следующие строки, так как они связаны с тарифными планами
// use VueFileManager\Subscription\Domain\Plans\Models\Plan;
// use VueFileManager\Subscription\Domain\Plans\Exceptions\MeteredBillingPlanDoesntExist;

class CreateNewUserAction extends Controller
{
    public function __construct(
        // Удалите зависимость от AutoSubscribeForMeteredBillingAction
        // protected AutoSubscribeForMeteredBillingAction $autoSubscribeForMeteredBilling,
    ) {
    }

    /**
     * Validate and create a new user.
     *
     * @throws \Exception
     */
    public function __invoke(CreateUserData $data): User
    {
        // Удалите вызов проверки тарифного плана
        // $this->checkMeteredBillingPlan($data);

        // Создание пользователя
        $user = $this->createUser($data);

        // Присоединение к ранее принятым приглашениям в командные папки
        $this->applyExistingTeamInvitations($user);

        // Удалите автоматическую подписку на metered billing
        // if (get_settings('subscription_type') === 'metered' && $data->role !== 'admin') {
        //     ($this->autoSubscribeForMeteredBilling)($user);
        // }

        // Отметить как верифицированного, если верификация отключена
        if (! $data->password || ! intval(get_settings('user_verification'))) {
            $user->markEmailAsVerified();
        }

        return $user;
    }

    /**
     * Удалите метод checkMeteredBillingPlan, так как он больше не нужен
     */
    // private function checkMeteredBillingPlan(CreateUserData $data): void
    // {
    //     if (get_settings('subscription_type') === 'metered' && $data->role !== 'admin') {
    //         // Get metered plan
    //         $plan = Plan::where('status', 'active')
    //             ->where('type', 'metered');

    //         if ($plan->doesntExist()) {
    //             throw new MeteredBillingPlanDoesntExist();
    //         }
    //     }
    // }

    private function applyExistingTeamInvitations(User $user): void
    {
        TeamFolderInvitation::where('email', $user->email)
            ->where('status', 'waiting-for-registration')
            ->cursor()
            ->each(function ($invitation) use ($user) {
                TeamFolderMember::create([
                    'user_id'    => $user->id,
                    'parent_id'  => $invitation->parent_id,
                    'permission' => $invitation->permission,
                ]);

                $invitation->accept();
            });
    }

    private function createUser(CreateUserData $data): User
    {
        $user = User::create([
            'password'       => $data->password ? bcrypt($data->password) : null,
            'oauth_provider' => $data->oauth_provider,
            'email'          => $data->email,
        ]);

        // Разделение имени
        $name = split_name($data->name);

        // Сохранение данных пользователя
        $user->settings()->create([
            'first_name' => $name['first_name'],
            'last_name'  => $name['last_name'],
            'avatar'     => $data->avatar,
        ]);

        return $user;
    }
}
