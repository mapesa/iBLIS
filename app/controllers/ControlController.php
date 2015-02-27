<?php

class ControlController extends \BaseController {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		$controls = Control::orderBy('id')->get();
		return View::make('control.index')->with('controls', $controls);
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		$instruments = Instrument::lists('name', 'id');
		$measureTypes = MeasureType::all();

		return View::make('control.create') ->with('instruments', $instruments) ->with('measureTypes', $measureTypes);
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
		//Validation -checking that name is unique among the un soft-deleted ones
		$rules = array('name' => 'required|unique:controls,name,NULL,id,deleted_at,null',
		 			'instrument' => 'required|non_zero_key',
		 			'new-measures' => 'required');
		$validator = Validator::make(Input::all(), $rules);

		if ($validator->fails()) {
			return Redirect::route('control.create')->withErrors($validator)->withInput();
		} else {
			// Add
			$control = new Control;
			$control->name = Input::get('name');
			$control->description = Input::get('description');
			$control->instrument_id = Input::get('instrument');

			if (Input::get('new-measures')) {
					$newMeasures = Input::get('new-measures');
					$controlMeasure = New ControlMeasureController;
					$controlMeasure->saveMeasuresRanges($newMeasures, $control);
			}
			// redirect
			return Redirect::to('control')
					->with('message', trans('messages.successfully-added-control'))
					->with('activeControl', $control ->id);
		}
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
		$instruments = Instrument::lists('name', 'id');
		$control = Control::find($id);
		$measureTypes = MeasureType::all();
		return View::make('control.edit')->with('control',$control)->with('instruments', $instruments)
				->with('measureTypes', $measureTypes);
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
		$rules = array(
			'name' => 'unique:controls,name,NULL,id,deleted_at,null',
			'instrument' => 'required|non_zero_key',
			'measures' => 'required',
		);
		$validator = Validator::make(Input::all(), $rules);

		// process the login
		if ($validator->fails()) {
			return Redirect::back()->withErrors($validator)->withInput();
		} else {
			// Update
			$control = Control::find($id);
			$control->name = Input::get('name');
			$control->description = Input::get('description');
			$control->instrument_id = Input::get('instrument');

			if (Input::get('new-measures')) {
				$inputNewMeasures = Input::get('new-measures');
				$measures = New ControlMeasureController;
				$measureIds = $measures->saveMeasuresRanges($inputNewMeasures, $control);
			}

			if (Input::get('measures')) {
				$inputMeasures = Input::get('measures');
				$measures = New ControlMeasureController;
				$measures->saveMeasuresRanges($inputMeasures, $control);
			}
			// redirect
			return Redirect::back()->with('message', trans('messages.success-updating-test-type'));
		}
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		//Delete the control
		$control = Control::find($id);
		$control->delete();
		// redirect
		return Redirect::route('control.index')->with('message', trans('messages.success-deleting-control'));
	}

	/**
	 * Return resultsindex page
	 *
	 * @return Response
	 */
	public function resultsIndex()
	{
		$controls = Control::all();
		return View::make('control.resultsIndex')->with('controls', $controls);
	}

	/**
	 * Return resultsindex page
	 *
	 * @return Response
	 */
	public function resultsEntry($controlId) 
	{
		$control = Control::find($controlId);
		$lotNumber = Lot::where('instrument_id', $control->instrument_id)->orderBy('id', 'desc')->first()->number;
		$instrumentName = Instrument::find($control->instrument_id)->name;
		return View::make('control.resultsEntry')->with('control', $control)->with('lotNumber', $lotNumber)
						->with('instrumentName', $instrumentName);
	}

	/** 
	* Saves control results
	* 
	* @param Input, result inputs
	* @return Validation errors or response
	*/
	public function saveResults($controlId)
	{
		//Validate
		$control = Control::find($controlId);

		foreach ($control->controlMeasures as $controlMeasure) {
			$controlResult = new ControlMeasureResult;
			$controlResult->control_id = $controlId;
			$controlResult->control_measure_id = $controlMeasure->id;
			$controlResult->results = Input::get('m_'.$controlMeasure->id);
			$controlResult->entered_by = Auth::user()->id;
			$controlResult->save();
		}
		return Redirect::route('control.resultsIndex')->with('message', trans('messages.success-adding-control-result'));
	}


}