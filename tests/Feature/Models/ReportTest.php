<?php

use App\Models\Photo;
use App\Models\Report;
use App\Models\User;

it('stores a removal request against a photo without a reporter', function () {
    $photo = Photo::factory()->create();

    $report = Report::factory()->anonymous()->for($photo, 'reportable')->create();

    expect($report->fresh())
        ->reporter->toBeNull()
        ->reportable->toBeInstanceOf(Photo::class)
        ->reportable->id->toBe($photo->id);
});

it('keeps a report when the user who filed it is deleted', function () {
    $reporter = User::factory()->create();
    $report = Report::factory()->for($reporter, 'reporter')->create();

    $reporter->delete();

    expect($report->fresh())->reporter_id->toBeNull();
});
