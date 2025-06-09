<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class TestTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:timezone';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test timezone settings for user registration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🕒 Тестирование временных зон BONUS5');
        $this->info('=====================================');

        // Показать текущую временную зону приложения
        $this->info('📍 Временная зона приложения: ' . config('app.timezone'));
        $this->info('📍 Текущее время сервера: ' . now()->format('Y-m-d H:i:s T'));
        $this->info('📍 Московское время: ' . now()->setTimezone('Europe/Moscow')->format('Y-m-d H:i:s T'));

        $this->newLine();

        // Создать тестового пользователя
        $testEmail = 'timezone-test-' . time() . '@bonus5.test';

        $this->info('🧪 Создание тестового пользователя...');

        $user = User::create([
            'name' => 'Тест Временная Зона',
            'email' => $testEmail,
            'city' => 'Москва',
            'password' => Hash::make('password123'),
        ]);

        $this->info('✅ Пользователь создан: ' . $user->email);

        // Показать временные метки
        $this->newLine();
        $this->info('📊 Временные метки в базе данных:');
        $this->info('   created_at: ' . $user->created_at->format('Y-m-d H:i:s T'));
        $this->info('   updated_at: ' . $user->updated_at->format('Y-m-d H:i:s T'));

        $this->newLine();
        $this->info('🌍 Временные метки в разных часовых поясах:');
        $this->info('   MSK: ' . $user->created_at->setTimezone('Europe/Moscow')->format('Y-m-d H:i:s T'));
        $this->info('   UTC: ' . $user->created_at->setTimezone('UTC')->format('Y-m-d H:i:s T'));

        // Тестирование API Resource
        $this->newLine();
        $this->info('🔄 Тестирование API Resource:');

        $resource = new \App\Http\Resources\UserResource($user);
        $resourceArray = $resource->toArray(request());

        $this->info('   API created_at: ' . $resourceArray['created_at']->format('Y-m-d H:i:s T'));
        $this->info('   API updated_at: ' . $resourceArray['updated_at']->format('Y-m-d H:i:s T'));

        // Очистка - удалить тестового пользователя
        $user->delete();
        $this->info('🗑️  Тестовый пользователь удален');

        $this->newLine();
        $this->info('✅ Тест временных зон завершен успешно!');

        if ($user->created_at->timezone->getName() === 'Europe/Moscow') {
            $this->info('🎉 Временная зона корректно настроена на Europe/Moscow');
        } else {
            $this->error('❌ Проблема с временной зоной: ' . $user->created_at->timezone->getName());
        }

        return Command::SUCCESS;
    }
}
