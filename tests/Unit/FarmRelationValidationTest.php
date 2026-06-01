<?php

namespace Tests\Unit;

use App\Http\Requests\AnimalRequest;
use App\Http\Requests\BreedingRecordRequest;
use Illuminate\Support\Facades\Validator;
use Tests\Support\EnsureFarmSchema;
use Tests\Support\FarmTestFixtures;
use Tests\TestCase;

class FarmRelationValidationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        EnsureFarmSchema::install();
    }

    public function test_breeding_record_rejects_female_from_another_farm(): void
    {
        $farmA = FarmTestFixtures::farm();
        $farmB = FarmTestFixtures::farm();
        $herdA = FarmTestFixtures::livestock($farmA);
        $herdB = FarmTestFixtures::livestock($farmB);
        $femaleOnB = FarmTestFixtures::animal($farmB, $herdB, 'female');

        $request = BreedingRecordRequest::create('/breeding/records', 'POST', [
            'farm_id' => $farmA->id,
            'female_animal_id' => $femaleOnB->id,
            'breeding_date' => now()->toDateString(),
            'breeding_type' => 'artificial_insemination',
            'animal_type' => 'cattle',
            'external_sire_name' => 'External sire',
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('female_animal_id', $validator->errors()->toArray());
    }

    public function test_breeding_record_accepts_female_on_same_farm(): void
    {
        $farm = FarmTestFixtures::farm();
        $herd = FarmTestFixtures::livestock($farm);
        $female = FarmTestFixtures::animal($farm, $herd, 'female');

        $request = BreedingRecordRequest::create('/breeding/records', 'POST', [
            'farm_id' => $farm->id,
            'female_animal_id' => $female->id,
            'breeding_date' => now()->toDateString(),
            'breeding_type' => 'artificial_insemination',
            'animal_type' => 'cattle',
            'external_sire_name' => 'External sire',
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertFalse($validator->fails());
    }

    public function test_animal_request_rejects_livestock_from_another_farm(): void
    {
        $farmA = FarmTestFixtures::farm();
        $farmB = FarmTestFixtures::farm();
        $herdB = FarmTestFixtures::livestock($farmB);

        $request = AnimalRequest::create('/animals', 'POST', [
            'farm_id' => $farmA->id,
            'livestock_id' => $herdB->id,
            'tag_number' => 'TAG-NEW-1',
            'name' => 'New animal',
            'gender' => 'female',
            'health_status' => 'Healthy',
            'lifecycle_status' => 'Active',
        ]);
        $request->setContainer(app())->setRedirector(app('redirect'));

        $validator = Validator::make($request->all(), $request->rules());

        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('livestock_id', $validator->errors()->toArray());
    }
}
