<?php

namespace App\Repositories;

use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Workstation;
use Carbon\Carbon;
use Auth;

class WorkstationReviewRepository
{
    /**
     * Display a listing of the resource.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Resources\WorkstationReview\WorkstationReviewCollection
     */
    public function index(Request $request, $id)
    {
        // fetch workstation
        $workstation = Workstation::findOrFail($id);

        $reviews = collect($workstation->reviews);

        // map through each review to attach their corresponding user details
        $reviews->map(function ($review, $key){
            // fetch user with their avatar details
            $user = User::with('avatar:id,storage_environment,file_path')
                        ->findOrFail($review->author_id, ['id','last_name', 'first_name']);
            // attach user with the key "author" to the review
            return $review->author = $user;
        });

        // fetch reviews data
        $data['total_no_of_reviews'] = $workstation->numberOfReviews();
        $data['total_no_of_ratings'] = $workstation->numberOfRatings();
        $data['average_rating'] = $workstation->averageRating(1);
        $data['reviews'] = $reviews;

        // return reviews data
        return $data;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Digikraaft\ReviewRating\Review
     */
    public function store(Request $request, $id)
    {
        // fetch workstation
        $workstation = Workstation::findOrFail($id);

        // save the review and rating
        $workstation->review($request->review, Auth::user(), $request->rating);

        //return the last review
        return $workstation->latestReview();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $workstation_id
     * @param  int  $review_id
     * @return void
     */
    public function destroy($workstation_id, $review_id)
    {
        // get review instance
        $review = DB::table('reviews')->where('id', $review_id)->get()->first();

        // return 401 if user is not the author of review
        if ($review->author_id !== Auth::id()) {
            return response(['error' => 'you do not have permission to delete the resource'], 401);
        }
        // fetch review and update the deleted_at column
        DB::table('reviews')
            ->where('id', $review_id)
            ->update(['deleted_at' => Carbon::now()]);
    }
}
