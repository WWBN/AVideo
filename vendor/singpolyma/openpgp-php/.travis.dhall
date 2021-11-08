let Prelude = https://prelude.dhall-lang.org/v17.0.0/package.dhall
let phpseclib = \(max: Natural) -> \(filter: (Natural -> Bool)) ->
	Prelude.List.map Natural Text
		(\(m: Natural) -> "PHPSECLIB='2.0.${Prelude.Natural.show m}'")
		(Prelude.List.filter Natural filter (Prelude.Natural.enumerate max))
let Exclusion = { php: Text, env: Text }
in
{
	language = "php",
	php = [
		"7.3",
		"7.4",
		"8.0"
	],
	dist = "xenial",
	env = [
		"PHPSECLIB='^2.0 !=2.0.8'"
	] # (phpseclib 28 (\(m: Natural) -> Prelude.Bool.not (Prelude.Natural.equal m 8))
	),
	matrix = {
		exclude = Prelude.List.concatMap Text Exclusion (\(php: Text) ->
			Prelude.List.map Text Exclusion (\(env: Text) ->
				{ php = php, env = env }
			) (phpseclib 7 (\(_: Natural) -> True))
		) ["7.3", "7.4", "8.0"],
		fast_finish = True
	},
	before_script = ''
	sed -i "s/\"phpseclib\/phpseclib\": \"[^\"]*/\"phpseclib\/phpseclib\": \"$PHPSECLIB/" composer.json && composer install --prefer-source''
}
