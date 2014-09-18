<?php

class PatientReportController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		// List all the active patients
		$patients = Patient::paginate(Config::get('kblis.page-items'));

		// Load the view and pass the patients
		return View::make('reports.patient.index')->with('patients', $patients);
	}

	/**
	 * Function to search for the value submitted.
	 *
	 * @return Response
	 */

	public function searched($keyword){
		return View::make('reports.patient.results')->with('patients', Patient::search($keyword));
	}

	public function search()
	{
		//
		$rules = array(
			'value'       => 'required'
			);
		$validator = Validator::make(Input::all(), $rules);
	
		//process
		if($validator->fails()){
			return Redirect::back()->withErrors($validator);
		}else{
			//search
			$keyword = Input::get('value');
			return Redirect::to('patientreport/results/'.$keyword);
		}
	}

	public function apiDatatable()
	{
		return Datatable::collection(Patient::all())
        ->showColumns('id','patient_number' ,'external_patient_number','gender')
        ->addColumn('actions', function($model) {
	      return '<a href=patientreport/'.$model->id.' class="btn btn-sm btn-info" title="View Report""><i class="glyphicon glyphicon-eye-open"></i> View Report </a> '.
	      ' <a href=patientreport/'.$model->id.'/tests class="btn btn-sm btn-primary" title="Select Tests"><i class="glyphicon glyphicon-folder-open"></i> Select Tests </a>'.
	      ' <a href=patientreport/'.$model->id.'/bill class="btn btn-sm btn-default" title="Generate Bill"><i class="glyphicon glyphicon-send"></i> Generate Bill </a>';
	    })
        ->searchColumns('id','name', 'patient_number' ,'external_patient_number','gender')
        ->orderColumns('id','patient_number' ,'external_patient_number','gender')
        ->make();
	}

	/**
	 * Show the report view.
	 *
	 * @return Response
	 */
	public function viewReport($patientID)
	{
		$patient_id = Input::get('patient');
		$date_from = Input::get('from');
		$date_to = Input::get('to');
		$pending_tests = Input::get('pending');
		$range_visualization = Input::get('range');
		if($pending_tests||$range_visualization){
			$patient = Patient::where('id', '=', $patient_id)
								->where(function($query_string) use ($patient, $date_from, $date_to, $pending_tests){
														$query_string->whereHas('visits', function($query_string) use ($date_from, $date_to){
															$query_string->whereBetween('visits.created_at', array($date_from, $date_to));
														})->whereHas('test', function($query_string) use ($pending_tests){
																$query_string->where('test_status_id', '=', $pending_tests);
															});
														});
								
									
		}
		else if($range_visualization){
			$patient = Patient::where('id', '=', $patient_id)
								->where(function($query_string) use ($date_from, $date_to, $pending_tests){
									$query_string->whereHas('visit', function($query_string) use ($date_from, $date_to){
										$query_string->whereBetween('visits.created_at', array($date_from, $date_to));
									});
								});
		}
		else{
			$patient = Patient::find($patientID);
		}
		return View::make('reports.patient.report')->with('patient', $patient);
	}

	public static function dateDiff($end)
	{
		$today=date('Y-m-d H:i:s');
	   $return = array();
	   
	   try {
	      $start = new DateTime($today);
	      $end = new DateTime($end);
	      $form = $start->diff($end);
	   } catch (Exception $e){
	      return $e->getMessage();
	   }
	   
	   $display = array('y'=>'year',
	               'm'=>'month',
	               'd'=>'day',
	               'h'=>'hour',
	               'i'=>'minute',
	               's'=>'second');
	   foreach($display as $key => $value){
	      if($form->$key > 0){
	         $return[] = $form->$key.' '.($form->$key > 1 ? $value.'s' : $value);
	      }
	   }
	   
	   return implode($return, ', ');
	}

	
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		//
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		//
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		//
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		//
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//
	}


}