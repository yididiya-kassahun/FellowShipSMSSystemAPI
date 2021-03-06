<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\groups;
use App\GroupContact;
use App\User;
use App\Contact;
use App\Fellowship;
use Input;
use Excel;
use DateTime;
use Carbon\Carbon;


class GroupController extends Controller
{
    public function __construct() {

        $this->middleware('auth:api');
    }

    public function addGroup() {
        try{
           $user=auth('api')->user();

            $request = request()->only('group_name', 'description');
            $rule = [
                'group_name' => 'required|string|max:255',
                'description' => 'required|string',            
            ]; 
            $validator = Validator::make($request, $rule);
            
           // $contact = Contact::find($phone_number);
            $group = new groups();

            $group->group_name = $request['group_name'];
            $group->description = $request['description'];
            $group->created_by = $user->first_name;
            $group->contacts_id = true;
            $group->fellowship_id = $user->fellowship_id;
           // $group->created_at=$request['created_at'];
        
            if($group->save()) {

                return response()->json(['message' => 'Group added successfully'], 200);
                 
            }
           
            return response()->json(['message' => 'Ooops! something went wrong', 'error' => 'something went wrong, unable to save the contact'], 500);
        }catch(Exception $ex) {
            return response()->json(['message' => 'Ooops! something went wrong', 'error' => $ex->getMessage()], 500);
        }
    }
 
    public function getGroup($id) {

        $user=auth('api')->user();
        try { 
           
         // """""""""" some foreign key conditions here """""""""""""
            $group = groups::where([['contacts_id', '=', 1],['fellowship_id','=',$user->fellowship_id]])->orderBy('group_id', 'asc')->paginate(10);
            
            $_count = DB::table('group_contacts')->where(
                [['contacts_id','=',$id],['fellowship_id','=',$user->fellowship_id]]
                )->count();
           
                return response()->json(['Groups' => $group,'contacts' =>$_count], 200);
            
            // return response()->json(['Groups' => $group], 200);
        } catch(Exception $ex) {
            return response()->json(['message' => 'Ooops! something went wrong', 'error' => $ex], 500);
        }
    }

    public function getGroups() {
        try {
            $user=auth('api')->user();

            $groups = groups::where('fellowship_id', '=', $user->fellowship_id)->get();
           // $group_name =$groups->group_name;
            //$countGroups = $groups->count();

                return response()->json(['Groups' => $groups], 200);
            for($i = 0; $i < $countGroups; $i++) {
                $groups[$i]->created_by = json_decode($groups[$i]->created_by);
            }
            return response()->json(['Groups' => $groups], 200);
        }catch(Exception $ex) {
            return repsonse()->josn(['message' => 'Ooops! something went wrong', 'error' => $ex->getMessage()], 500);
        }
    }

    public function deleteGroup($id) {
        try {
            $user=auth('api')->user();

            $group = groups::find($id);

            if($group instanceof groups && $group->group_id == $user->fellowship_id) {

                if($group->delete()) {
                    return response()->json(['message' => 'contact deleted successfully'], 200);
                }
                return response()->json(['message' => 'Ooops! something went wrong', 'error' => 'unable to delete contact'], 500);
            } 
            return response()->json(['message' => 'an error occurred', 'error' => 'contact is not found'], 404);
        } catch(Exception $ex) {
            return response()->json(['message' => 'Ooops! something went wrong', 'error' => $ex->getMessage()], 500);
        }
    }

}
