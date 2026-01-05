<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | The following language lines contain the default error messages used by
    | the validator class. Some of these rules have multiple versions such
    | as the size rules. Feel free to tweak each of these messages here.
    |
    */

    'accepted' => 'Mae\'r maes :attribute yn rhaid ei dderbyn.',
    'accepted_if' => 'Mae\'r maes :attribute yn rhaid ei dderbyn 
        pan fo :other yn :value.',
    'active_url' => 'Mae\'r maes :attribute yn rhaid i fod yn URL dilys.',
    'after' => 'Mae\'r maes :attribute yn rhaid i fod yn ddyddiad 
        ar ôl :date.',
    'after_or_equal' => 'Mae\'r maes :attribute yn rhaid i fod yn ddyddiad 
        ar neu ar ôl :date.',
    'alpha' => 'Mae\'r maes :attribute yn rhaid i gynnwys llythrennau yn unig.',
    'alpha_dash' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
        llythrennau, rhifau, dashys, ac underscores yn unig.',
    'aplha_num' => 'Mae\'r maes :attribute yn rhaid i gynnwys
        llythrennau a rhifau yn unig.',
    'any_of' => 'Mae\'r maes :attribute yn annilys.',
    'array' => 'Mae\'r maes :attribute yn rhaid i fod yn arae.',
    'ascii' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
        nodau alffaniwmerig a symbolau un-bait yn unig.',
    'before' => 'Mae\'r maes :attribute yn rhaid i fod yn ddyddiad 
        cyn :date.',
    'before_or_equal' => 'Mae\'r maes :attribute yn rhaid i fod yn ddyddiad 
        cyn neu ar :date.',
    'between' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys rhwng 
            :min a :max eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod rhwng :min a :max 
            kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod rhwng :min a :max.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod rhwng :min a :max 
            nodyn.',
    ],
    'boolean' => 'Mae\'r maes :attribute yn rhaid i fod yn wir neu 
        yn ffug.',
    'can' => 'Mae\'r maes :attribute yn cynnwys gwerth 
        heb awdurdod.',
    'confirmed' => 'Nid yw cadarnhad y maes :attribute yn cyfateb.',
    'contains' => 'Mae\'r maes :attribute yn colli gwerth 
        gofynnol.',
    'current_password' => 'Mae\'r cyfrinair yn anghywir.',
    'date' => 'Mae\'r maes :attribute yn rhaid i fod yn ddyddiad dilys.',
    'date_equals' => 'Mae\'r maes :attribute yn rhaid i fod yn ddyddiad 
        sy\'n hafal i :date.',
    'date_format' => 'Mae\'r maes :attribute yn rhaid i gyd-fynd 
        â\'r fformat :format.',
    'decimal' => 'Mae\'r maes :attribute yn rhaid i gael :decimal 
        lleoedd degol.',
    'declined' => 'Mae\'r maes :attribute yn rhaid ei wrthod.',
    'declined_if' => 'Mae\'r maes :attribute yn rhaid ei wrthod 
        pan fo :other yn :value.',
    'different' => 'Mae\'r maes :attribute a :other yn rhaid i fod yn wahanol.',
    'digits' => 'Mae\'r maes :attribute yn rhaid i fod yn :digits digid.',
    'digits_between' => 'Mae\'r maes :attribute yn rhaid i fod rhwng 
        :min a :max digid.',
    'dimensions' => 'Mae\r maes :attribute yn cael dimensiynau 
        delwedd annilys.',
    'distinct' => 'Mae\'r maes :attribute yn cynnwys gwerth
        dyblyg.',
    'distinct_contain' => 'Mae\'r maes :attribute yn rhaid 
        peidio â chynnwys unrhyw un o\'r canlynol: :values.',
    'doesnt_end_with' => 'Mae\'r maes :attribute yn rhaid 
        peidio â gorffen gyda un o\'r canlynol: :values.',
    'doesnt_start_with' => 'Mae\'r maes :attribute yn rhaid
        peidio â dechrau gyda un o\'r canlynol: :values.',
    'email' => 'Mae\'r maes :attribute yn rhaid i fod yn gyfeiriad e-bost dilys.',
    'ends_with' => 'Mae\'r maes :attribute yn rhaid i orffen gyda un o\'r 
        canlynol: :values.',
    'enum' => 'Mae\'r dewis :attribute yn annilys.',
    'exists' => 'Mae\'r dewis :attribute yn annilys.',
    'extensions' => 'Mae\'r maes :attribute yn rhaid i gael un o\'r 
        estyniadau canlynol: :values.',
    'file' => 'Mae\'r maes :attribute yn rhaid i fod yn ffeil.',
    'filed' => 'Mae\'r maes :attribute yn rhaid i gael gwerth.',
    'gt' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
            mwy na :value eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod yn fwy na 
            :value kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod yn fwy na :value.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod yn fwy na 
            :value nodyn.',
    ],
    'gte' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
            :value eitemau neu fwy.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod yn fwy
            na neu yn hafal i :value kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod yn fwy 
            na neu yn hafal i :value.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod yn fwy 
            na neu yn hafal i :value nodyn.',
    ],
    'hex_color' => 'Mae\'r maes :attribute yn rhaid i fod yn liw 
        hecsadegol dilys.',
    'image' => 'Mae\'r maes :attribute yn rhaid i fod yn ddelwedd.',
    'in' => 'Mae\'r dewis :attribute yn annilys.',
    'in_array' => 'Mae\'r maes :attribute yn rhaid i fod yn 
        bodoli yn :other.',
    'in_array_keys' => 'Mae\'r maes :attribute yn rhaid i gynnwys
        o leiaf un o\'r allweddi canlynol: :values.',
    'integer' => 'Mae\'r maes :attribute yn rhaid i fod yn gyfanrif.',
    'ip' => 'Mae\'r maes :attribute yn rhaid i fod yn gyfeiriad IP dilys.',
    'ipv4' => 'Mae\'r maes :attribute yn rhaid i fod yn gyfeiriad 
        IPv4 dilys.',
    'ipv6' => 'Mae\'r maes :attribute yn rhaid i fod yn gyfeiriad
        IPv6 dilys.',
    'json' => 'Mae\'r maes :attribute yn rhaid i fod yn llinyn JSON dilys.',
    'list' => 'Mae\'r maes :attribute yn rhaid i fod yn rhestr.',
    'lowercase' => 'Mae\'r maes :attribute yn rhaid i fod yn 
        llythrennau bach.',
    'lt' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
            llai na :value eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod yn llai na 
            :value kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod yn llai na :value.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod yn llai na 
            :value nodyn.',
    ],
    'lte' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
            ddim mwy na :value eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod yn llai
            na neu yn hafal i :value kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod yn llai
            na neu yn hafal i :value.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod yn llai
            na neu yn hafal i :value nodyn.',
    ],
    'mac_address' => 'Mae\r maes :attribute yn rhaid i fod yn gyfeiriad 
        MAC dilys.',
    'max' => [ 
        'array' => 'Mae\'r maes :attribute yn rhaid i beidio 
            â chael mwy na :max eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i beidio â bod 
            yn fwy na :max kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i beidio 
            â bod yn fwy na :max.',
        'string' => 'Mae\'r maes :attribute yn rhaid i beidio 
            â bod yn fwy na :max nodyn.',
    ],
    'max_digits' => 'Mae\'r maes :attribute yn rhaid i beidio â chael 
        mwy na :max digid.',
    'mimes' => 'Mae\'r maes :attribute yn rhaid i fod yn ffeil o\'r
        mathau canlynol: :values.',
    'mimetypes' => 'Mae\'r maes :attribute yn rhaid i fod yn ffeil 
        o\'r mathau canlynol: :values.',
    'min' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
            o leiaf :min eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod o leiaf
            :min kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod o leiaf :min.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod o leiaf 
            :min nodyn.',
    ],
    'min_digits' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
        o leiaf :min digid.',
    'missing' => 'Mae\'r maes :attribute yn rhaid i fod ar goll.',
    'missing_if' => 'Mae\'r maes :attribute yn rhaid i fod ar goll 
        pan fo :other yn :value.',
    'missing_unless' => 'Mae\'r maes :attribute yn rhaid i fod ar goll 
        oni bai bod :other yn :value.',
    'missing_with' => 'Mae\'r maes :attribute yn rhaid i fod ar goll
        pan fo :values yn bresennol.',
    'missing_with_all' => 'Mae\'r maes :attribute yn rhaid i fod ar goll
        pan fo :values yn bresennol.',
    'multiple_of' => 'Mae\'r maes :attribute yn rhaid i fod yn llu o :value.',
    'not_in' => 'Mae\'r dewis :attribute yn annilys.',
    'not_regex' => 'Mae\'r fformat maes :attribute yn annilys.',
    'numeric' => 'Mae\'r maes :attribute yn rhaid i fod yn rhif.',
    'password' => [
        'letters' => 'Mae\'r maes :attribute yn rhaid i gynnwys o leiaf 
            un llythyren.',
        'mixed' => 'Mae\'r maes :attribute yn rhaid i gynnwys o leiaf 
                un llythyren fawr ac un llythyren fach.',
        'numbers' => 'Mae\'r maes :attribute yn rhaid i gynnwys o leiaf
            un rhif.',
        'symbols' => 'Mae\'r maes :attribute yn rhaid i gynnwys o leiaf
            un symbol.',
        'uncompromised' => 'Mae\'r :attribute a roddwyd wedi ymddangos
            mewn datgeliad data. Dewiswch :attribute gwahanol os gwelwch yn dda.',
    ],
    'present' => 'Mae\'r maes :attribute yn rhaid i fod yn bresennol.',
    'present_if' => 'Mae\'r maes :attribute yn rhaid i fod yn bresennol 
        pan fo :other yn :value.',
    'present_unless' => 'Mae\'r maes :attribute yn rhaid i fod yn bresennol
        oni bai bod :other yn :value.',
    'present_with' => 'Mae\'r maes :attribute yn rhaid i fod yn bresennol
        pan fo :values yn bresennol.',
    'present_with_all' => 'Mae\'r maes :attribute yn rhaid i fod yn
        bresennol pan fo :values yn bresennol.',
    'prohibited' => 'Mae\'r maes :attribute yn waharddedig.',
    'prohibited_if' => 'Mae\'r maes :attribute yn waharddedig 
        pan fo :other yn :value.',
    'prohibited_if_accepted' => 'Mae\'r maes :attribute yn waharddedig
        pan fo :other yn cael ei dderbyn.',
    'prohibited_if_declined' => 'Mae\'r maes :attribute yn waharddedig
        pan fo :other yn cael ei wrthod.',
    'prohibited_unless' => 'Mae\'r maes :attribute yn waharddedig
        oni bai bod :other yn un o :values.',
    'prohibits' => 'Mae\'r maes :attribute yn gwahardd bod 
        yn bresennol gyda :other.',
    'regex' => 'Mae fformat y maes :attribute yn annilys.',
    'required' => 'Mae\'r maes :attribute yn ofynnol.',
    'required_array_keys' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
        eitemau ar gyfer: :values.',
    'required_if' => 'Mae\'r maes :attribute yn ofynnol 
        pan fo :other yn :value.',
    'required_if_accepted' => 'Mae\'r maes :attribute yn ofynnol
        pan fo :other yn cael ei dderbyn.',
    'required_if_declined' => 'Mae\'r maes :attribute yn ofynnol
        pan fo :other yn cael ei wrthod.',
    'required_unless' => 'Mae\'r maes :attribute yn ofynnol
        oni bai bod :other yn un o :values.',
    'required_with' => 'Mae\'r maes :attribute yn ofynnol 
        pan fo :values yn bresennol.',
    'required_with_all' => 'Mae\'r maes :attribute yn ofynnol
        pan fo :values yn bresennol.',
    'required_without' => 'Mae\'r maes :attribute yn ofynnol
        pan nad yw :values yn bresennol.',
    'required_without_all' => 'Mae\'r maes :attribute yn ofynnol
        pan nad oes unrhyw un o :values yn bresennol.',
    'same' => 'Mae\'r maes :attribute yn rhaid i gyd-fynd â :other.',
    'size' => [
        'array' => 'Mae\'r maes :attribute yn rhaid i gynnwys 
            :size eitem.',
        'file' => 'Mae\'r maes :attribute yn rhaid i fod yn
            :size kilobytes.',
        'numeric' => 'Mae\'r maes :attribute yn rhaid i fod yn :size.',
        'string' => 'Mae\'r maes :attribute yn rhaid i fod yn 
            :size nodyn.',
    ],
    'starts_with' => 'Mae\'r maes :attribute yn rhaid i ddechrau gyda un o\'r 
        canlynol: :values.',
    'string' => 'Mae\'r maes :attribute yn rhaid i fod yn llinyn.',
    'timezone' => 'Mae\'r maes :attribute yn rhaid i fod yn 
        barth amser dilys.',
    'unique' => 'Mae\'r maes :attribute eisoes wedi\'i gymryd.',
    'uploaded' => 'Mae\'r maes :attribute wedi methu â 
        llwytho i fyny.',
    'uppercase' => 'Mae\'r maes :attribute yn rhaid i fod yn 
        llythrennau mawr.',
    'url' => 'Mae\'r maes :attribute yn rhaid i fod yn URL dilys.',
    'ulid' => 'Mae\'r maes :attribute yn rhaid i fod yn ULID dilys.',
    'uuid' => 'Mae\'r maes :attribute yn rhaid i fod yn UUID dilys.',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    |
    | Here you may specify custom validation messages for attributes using the
    | convention "attribute.rule" to name the lines. This makes it quick to
    | specify a specific custom language line for a given attribute rule.
    |
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    |
    | The following language lines are used to swap our attribute placeholder
    | with something more reader friendly such as "E-Mail Address" instead
    | of "email". This simply helps us make our message more expressive.
    |
    */

    'attributes' => [],

];
