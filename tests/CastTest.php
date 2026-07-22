<?php

namespace Remls\HijriDate\Tests;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Remls\HijriDate\HijriDate;

class CastTestModel extends Model
{
    protected $guarded = [];
    protected $casts = [
        'dob' => HijriDate::class,
    ];
}

final class CastTest extends TestCase
{
    public function test_get_casts_stored_string_to_hijri_date(): void
    {
        $model = new CastTestModel(['dob' => '1444-01-15']);

        $this->assertInstanceOf(HijriDate::class, $model->dob);
        $this->assertSame('1444-01-15', $model->dob->toDateString());
    }

    public function test_set_from_hijri_date_stores_string(): void
    {
        $model = new CastTestModel();
        $model->dob = new HijriDate(1444, 2, 1);

        $this->assertSame('1444-02-01', $model->getAttributes()['dob']);
    }

    public function test_set_from_string_stores_string(): void
    {
        $model = new CastTestModel();
        $model->dob = '1444-03-01';

        $this->assertSame('1444-03-01', $model->getAttributes()['dob']);
    }

    public function test_set_invalid_string_throws(): void
    {
        $model = new CastTestModel();

        $this->expectException(InvalidArgumentException::class);
        $model->dob = 'not a date';
    }

    public function test_null_round_trips(): void
    {
        $model = new CastTestModel(['dob' => null]);

        $this->assertNull($model->dob);
        $this->assertNull($model->getAttributes()['dob']);
    }

    public function test_serialization(): void
    {
        $model = new CastTestModel(['dob' => '1444-01-15']);

        $this->assertSame('1444-01-15', $model->toArray()['dob']);
    }
}
